<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReferralController extends Controller
{
    /**
     * Display a listing of referrals.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Referral::class);
        
        $query = Referral::with(['referrer', 'referred']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Search by referral code or user name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('referral_code', 'like', "%{$search}%")
                    ->orWhereHas('referrer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('referred', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }
        
        $referrals = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get statistics
        $stats = [
            'total' => Referral::count(),
            'pending' => Referral::pending()->count(),
            'completed' => Referral::completed()->count(),
            'expired' => Referral::expired()->count(),
            'cancelled' => Referral::cancelled()->count(),
            'total_rewards' => Referral::completed()->sum('reward_amount'),
            'total_referred_rewards' => Referral::completed()->sum('referred_reward_amount'),
            'unclaimed_rewards' => Referral::unclaimedRewards()->count(),
        ];
        
        // Get referral settings
        $settings = Setting::first();
        
        return view('admin.referrals.index', compact('referrals', 'stats', 'settings'));
    }

    /**
     * Show the form for creating a new referral.
     */
    public function create()
    {
        $this->authorize('create', Referral::class);
        
        $users = User::where('user_role', 'user')
            ->where('is_approved', true)
            ->orderBy('name')
            ->get();
        
        $settings = Setting::first();
        
        return view('admin.referrals.create', compact('users', 'settings'));
    }

    /**
     * Store a newly created referral.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Referral::class);
        
        $validated = $request->validate([
            'referrer_id' => 'required|exists:users,id',
            'referred_id' => 'nullable|exists:users,id|different:referrer_id',
            'reward_amount' => 'required|numeric|min:0',
            'referred_reward_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,completed,expired,cancelled',
            'expires_at' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:500',
        ]);
        
        // Generate unique referral code
        $validated['referral_code'] = Referral::generateReferralCode();
        
        // Set completed_at if status is completed
        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        }
        
        // Set default expiry if not provided
        if (empty($validated['expires_at'])) {
            $settings = Setting::first();
            $expiryDays = $settings->referral_expiry_days ?? 30;
            $validated['expires_at'] = now()->addDays($expiryDays);
        }
        
        Referral::create($validated);
        
        return redirect()->route('admin.referrals.index')
            ->with('success', 'Referral created successfully.');
    }

    /**
     * Display the specified referral.
     */
    public function show(Referral $referral)
    {
        $this->authorize('view', $referral);
        
        $referral->load(['referrer', 'referred']);
        
        return view('admin.referrals.show', compact('referral'));
    }

    /**
     * Show the form for editing the specified referral.
     */
    public function edit(Referral $referral)
    {
        $this->authorize('update', $referral);
        
        $users = User::where('user_role', 'user')
            ->where('is_approved', true)
            ->orderBy('name')
            ->get();
        
        return view('admin.referrals.edit', compact('referral', 'users'));
    }

    /**
     * Update the specified referral.
     */
    public function update(Request $request, Referral $referral)
    {
        $this->authorize('update', $referral);
        
        $validated = $request->validate([
            'referrer_id' => 'required|exists:users,id',
            'referred_id' => 'nullable|exists:users,id|different:referrer_id',
            'reward_amount' => 'required|numeric|min:0',
            'referred_reward_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,completed,expired,cancelled',
            'reward_claimed' => 'boolean',
            'referred_reward_claimed' => 'boolean',
            'expires_at' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);
        
        // Handle checkbox values
        $validated['reward_claimed'] = $request->has('reward_claimed');
        $validated['referred_reward_claimed'] = $request->has('referred_reward_claimed');
        
        // Set completed_at if status changed to completed
        if ($validated['status'] === 'completed' && $referral->status !== 'completed') {
            $validated['completed_at'] = now();
        }
        
        $referral->update($validated);
        
        return redirect()->route('admin.referrals.index')
            ->with('success', 'Referral updated successfully.');
    }

    /**
     * Remove the specified referral.
     */
    public function destroy(Referral $referral)
    {
        $this->authorize('delete', $referral);
        
        $referral->delete();
        
        return redirect()->route('admin.referrals.index')
            ->with('success', 'Referral deleted successfully.');
    }

    /**
     * Update referral status.
     */
    public function updateStatus(Request $request, Referral $referral)
    {
        $this->authorize('update', $referral);
        
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,expired,cancelled',
        ]);
        
        $referral->update([
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === 'completed' ? now() : $referral->completed_at,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Referral status updated successfully.',
            'status' => $referral->status,
        ]);
    }

    /**
     * Mark reward as claimed and credit to user's wallet.
     */
    public function claimReward(Request $request, Referral $referral)
    {
        $this->authorize('update', $referral);
        
        $validated = $request->validate([
            'type' => 'required|in:referrer,referred',
        ]);
        
        // Check if referral is completed
        if (!$referral->isCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Referral must be completed before claiming rewards.',
            ], 400);
        }
        
        if ($validated['type'] === 'referrer') {
            // Check if already claimed
            if ($referral->reward_claimed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Referrer reward has already been claimed.',
                ], 400);
            }
            
            // Credit reward to referrer's wallet
            $referrer = $referral->referrer;
            if ($referrer && $referral->reward_amount > 0) {
                $referrer->creditWallet(
                    $referral->reward_amount,
                    "Referral reward for code {$referral->referral_code}",
                    'referral',
                    $referral->id
                );
            }
            
            $referral->claimReferrerReward();
            $message = 'Referrer reward of ₹' . number_format($referral->reward_amount, 2) . ' credited to wallet.';
        } else {
            // Check if already claimed
            if ($referral->referred_reward_claimed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Referred user reward has already been claimed.',
                ], 400);
            }
            
            // Credit reward to referred user's wallet
            $referred = $referral->referred;
            if ($referred && $referral->referred_reward_amount > 0) {
                $referred->creditWallet(
                    $referral->referred_reward_amount,
                    "Welcome reward for joining via referral code {$referral->referral_code}",
                    'referral',
                    $referral->id
                );
            }
            
            $referral->claimReferredReward();
            $message = 'Referred user reward of ₹' . number_format($referral->referred_reward_amount, 2) . ' credited to wallet.';
        }
        
        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
    
    /**
     * Claim both rewards at once.
     */
    public function claimAllRewards(Referral $referral)
    {
        $this->authorize('update', $referral);
        
        if (!$referral->isCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Referral must be completed before claiming rewards.',
            ], 400);
        }
        
        $messages = [];
        
        // Claim referrer reward
        if (!$referral->reward_claimed && $referral->referrer && $referral->reward_amount > 0) {
            $referral->referrer->creditWallet(
                $referral->reward_amount,
                "Referral reward for code {$referral->referral_code}",
                'referral',
                $referral->id
            );
            $referral->claimReferrerReward();
            $messages[] = 'Referrer reward credited.';
        }
        
        // Claim referred user reward
        if (!$referral->referred_reward_claimed && $referral->referred && $referral->referred_reward_amount > 0) {
            $referral->referred->creditWallet(
                $referral->referred_reward_amount,
                "Welcome reward for joining via referral code {$referral->referral_code}",
                'referral',
                $referral->id
            );
            $referral->claimReferredReward();
            $messages[] = 'Referred user reward credited.';
        }
        
        if (empty($messages)) {
            return response()->json([
                'success' => false,
                'message' => 'No rewards to claim.',
            ], 400);
        }
        
        return response()->json([
            'success' => true,
            'message' => implode(' ', $messages),
        ]);
    }

    /**
     * Update referral settings.
     */
    public function updateSettings(Request $request)
    {
        $this->authorize('create', Referral::class);
        
        $validated = $request->validate([
            'referral_enabled' => 'boolean',
            'referral_reward_amount' => 'required|numeric|min:0',
            'referred_reward_amount' => 'required|numeric|min:0',
            'referral_expiry_days' => 'required|integer|min:1|max:365',
            'referral_min_order_amount' => 'required|numeric|min:0',
        ]);
        
        $validated['referral_enabled'] = $request->has('referral_enabled');
        
        $settings = Setting::first();
        $settings->update($validated);
        
        return redirect()->route('admin.referrals.index')
            ->with('success', 'Referral settings updated successfully.');
    }

    /**
     * Generate referral codes for a user.
     */
    public function generateCode(Request $request)
    {
        $this->authorize('create', Referral::class);
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'count' => 'integer|min:1|max:10',
        ]);
        
        $settings = Setting::first();
        $count = $validated['count'] ?? 1;
        $codes = [];
        
        for ($i = 0; $i < $count; $i++) {
            $referral = Referral::create([
                'referrer_id' => $validated['user_id'],
                'referral_code' => Referral::generateReferralCode(),
                'status' => 'pending',
                'reward_amount' => $settings->referral_reward_amount ?? 100,
                'referred_reward_amount' => $settings->referred_reward_amount ?? 50,
                'expires_at' => now()->addDays($settings->referral_expiry_days ?? 30),
            ]);
            $codes[] = $referral->referral_code;
        }
        
        return response()->json([
            'success' => true,
            'message' => count($codes) . ' referral code(s) generated successfully.',
            'codes' => $codes,
        ]);
    }

    /**
     * Export referrals to CSV.
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', Referral::class);
        
        $query = Referral::with(['referrer', 'referred']);
        
        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $referrals = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'referrals_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function () use ($referrals) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, [
                'ID',
                'Referral Code',
                'Referrer Name',
                'Referrer Email',
                'Referred Name',
                'Referred Email',
                'Status',
                'Reward Amount',
                'Referred Reward Amount',
                'Reward Claimed',
                'Referred Reward Claimed',
                'Completed At',
                'Expires At',
                'Created At',
            ]);
            
            foreach ($referrals as $referral) {
                fputcsv($file, [
                    $referral->id,
                    $referral->referral_code,
                    $referral->referrer->name ?? 'N/A',
                    $referral->referrer->email ?? 'N/A',
                    $referral->referred->name ?? 'N/A',
                    $referral->referred->email ?? 'N/A',
                    ucfirst($referral->status),
                    $referral->reward_amount,
                    $referral->referred_reward_amount,
                    $referral->reward_claimed ? 'Yes' : 'No',
                    $referral->referred_reward_claimed ? 'Yes' : 'No',
                    $referral->completed_at?->format('Y-m-d H:i:s') ?? 'N/A',
                    $referral->expires_at?->format('Y-m-d H:i:s') ?? 'N/A',
                    $referral->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
