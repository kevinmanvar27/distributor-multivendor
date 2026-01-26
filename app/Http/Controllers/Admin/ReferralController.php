<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    /**
     * Display a listing of referrals.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Referral::class);
        
        $query = Referral::query();
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Search by name or referral code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('referral_code', 'like', "%{$search}%");
            });
        }
        
        $referrals = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get statistics
        $stats = [
            'total' => Referral::count(),
            'active' => Referral::active()->count(),
            'inactive' => Referral::inactive()->count(),
        ];
        
        return view('admin.referrals.index', compact('referrals', 'stats'));
    }

    /**
     * Show the form for creating a new referral.
     */
    public function create()
    {
        $this->authorize('create', Referral::class);
        
        return view('admin.referrals.create');
    }

    /**
     * Store a newly created referral.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Referral::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);
        
        // Generate unique referral code
        $validated['referral_code'] = Referral::generateReferralCode();
        
        Referral::create($validated);
        
        return redirect()->route('admin.referrals.index')
            ->with('success', 'Referral code created successfully.');
    }

    /**
     * Show the form for editing the specified referral.
     */
    public function edit(Referral $referral)
    {
        $this->authorize('update', $referral);
        
        return view('admin.referrals.edit', compact('referral'));
    }

    /**
     * Update the specified referral.
     */
    public function update(Request $request, Referral $referral)
    {
        $this->authorize('update', $referral);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);
        
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
     * Update referral status (AJAX).
     */
    public function updateStatus(Request $request, Referral $referral)
    {
        $this->authorize('update', $referral);
        
        $validated = $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        
        $referral->update([
            'status' => $validated['status'],
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Referral status updated successfully.',
            'status' => $referral->status,
        ]);
    }
}
