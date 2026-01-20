<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Salary;
use App\Models\SalaryPayment;
use App\Models\Attendance;
use App\Models\User;
use App\Models\VendorStaff;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalaryController extends Controller
{
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Get staff users for this vendor
     */
    private function getVendorStaff()
    {
        $vendor = $this->getVendor();
        
        // Get user IDs from vendor_staff table
        $staffUserIds = VendorStaff::where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->pluck('user_id');
        
        return User::whereIn('id', $staffUserIds)->orderBy('name')->get();
    }

    /**
     * Display salary listing for vendor's staff.
     */
    public function index(Request $request)
    {
        $vendor = $this->getVendor();
        
        $staffUsers = $this->getVendorStaff();
        
        // Load salaries for each staff
        $staffUsers->load(['salaries' => function ($query) use ($vendor) {
            $query->where('vendor_id', $vendor->id)
                  ->where('is_active', true)
                  ->orderBy('effective_from', 'desc');
        }]);
        
        // Calculate stats
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        // Total monthly budget (sum of all active salaries)
        $totalMonthlyBudget = Salary::where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->sum('base_salary');
        
        // Total paid this month - get staff user IDs for this vendor
        $staffUserIds = VendorStaff::where('vendor_id', $vendor->id)->pluck('user_id');
        
        $totalPaidThisMonth = SalaryPayment::whereIn('user_id', $staffUserIds)
            ->whereMonth('payment_date', $currentMonth)
            ->whereYear('payment_date', $currentYear)
            ->where('status', 'paid')
            ->sum('net_salary');
        
        // Total pending (monthly budget - paid this month)
        $totalPending = max(0, $totalMonthlyBudget - $totalPaidThisMonth);
        
        return view('vendor.salary.index', compact('staffUsers', 'totalMonthlyBudget', 'totalPaidThisMonth', 'totalPending'));
    }

    /**
     * Show form for setting/updating staff salary.
     */
    public function create(Request $request)
    {
        $vendor = $this->getVendor();
        $userId = $request->get('user_id');
        
        $staffUsers = $this->getVendorStaff();
        $user = $userId ? $staffUsers->firstWhere('id', $userId) : null;
        
        // Get salary history for the user
        $salaryHistory = $user ? Salary::where('user_id', $user->id)
                                       ->where('vendor_id', $vendor->id)
                                       ->orderBy('effective_from', 'desc')
                                       ->get() : collect();
        
        return view('vendor.salary.create', compact('user', 'staffUsers', 'salaryHistory'));
    }

    /**
     * Store a new salary record.
     */
    public function store(Request $request)
    {
        $vendor = $this->getVendor();
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'base_salary' => 'required|numeric|min:0',
            'working_days_per_month' => 'required|integer|min:1|max:31',
            'effective_from' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Verify user is vendor's staff
        $isStaff = VendorStaff::where('vendor_id', $vendor->id)
            ->where('user_id', $request->user_id)
            ->where('is_active', true)
            ->exists();
            
        if (!$isStaff) {
            return redirect()->back()->with('error', 'Invalid staff member.');
        }
        
        DB::transaction(function () use ($request, $vendor) {
            // Deactivate previous active salary
            $previousSalary = Salary::where('user_id', $request->user_id)
                                    ->where('vendor_id', $vendor->id)
                                    ->where('is_active', true)
                                    ->first();
            
            if ($previousSalary) {
                $effectiveFrom = Carbon::parse($request->effective_from);
                $previousSalary->effective_to = $effectiveFrom->copy()->subDay();
                $previousSalary->is_active = false;
                $previousSalary->save();
            }
            
            // Create new salary record
            Salary::create([
                'user_id' => $request->user_id,
                'vendor_id' => $vendor->id,
                'base_salary' => $request->base_salary,
                'working_days_per_month' => $request->working_days_per_month,
                'effective_from' => $request->effective_from,
                'is_active' => true,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);
        });
        
        return redirect()->route('vendor.salary.index')->with('success', 'Salary updated successfully.');
    }

    /**
     * Show salary details for a staff member.
     */
    public function show($userId)
    {
        $vendor = $this->getVendor();
        
        // Verify user is vendor's staff
        $isStaff = VendorStaff::where('vendor_id', $vendor->id)
            ->where('user_id', $userId)
            ->exists();
            
        if (!$isStaff) {
            abort(403, 'Unauthorized access to this staff member.');
        }
        
        $user = User::findOrFail($userId);
        $activeSalary = Salary::where('user_id', $userId)
            ->where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->first();
        $salaryHistory = Salary::where('user_id', $userId)
            ->where('vendor_id', $vendor->id)
            ->orderBy('effective_from', 'desc')
            ->get();
        
        return view('vendor.salary.show', compact('user', 'activeSalary', 'salaryHistory'));
    }

    /**
     * Show salary payments/payroll page.
     */
    public function payments(Request $request)
    {
        $vendor = $this->getVendor();
        
        $month = (int) $request->get('month', Carbon::now()->month);
        $year = (int) $request->get('year', Carbon::now()->year);
        $refresh = $request->get('refresh', false);
        
        $staffUsers = $this->getVendorStaff();
        
        $payments = [];
        foreach ($staffUsers as $user) {
            // Find or create payment record
            $payment = SalaryPayment::firstOrNew([
                'user_id' => $user->id,
                'month' => $month,
                'year' => $year,
            ]);
            
            if (!$payment->exists) {
                $payment->user_id = $user->id;
                $payment->month = $month;
                $payment->year = $year;
            }
            
            // Calculate from attendance if new or refresh requested
            if (!$payment->exists || $refresh) {
                $payment->calculateFromAttendance();
                $payment->save();
            }
            
            $payment->setRelation('user', $user);
            $payments[] = $payment;
        }
        
        // Calculate totals
        $totals = [
            'total_earned' => collect($payments)->sum('earned_salary'),
            'total_deductions' => collect($payments)->sum('deductions'),
            'total_bonus' => collect($payments)->sum('bonus'),
            'total_net' => collect($payments)->sum('net_salary'),
            'total_paid' => collect($payments)->sum('paid_amount'),
            'total_pending' => collect($payments)->sum('pending_amount'),
        ];
        
        return view('vendor.salary.payments', compact('payments', 'month', 'year', 'totals'));
    }

    /**
     * Process salary payment.
     */
    public function processPayment(Request $request, $id)
    {
        $vendor = $this->getVendor();
        
        $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'transaction_id' => 'nullable|string|max:100',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $payment = SalaryPayment::findOrFail($id);
        
        // Verify payment belongs to vendor's staff
        $isStaff = VendorStaff::where('vendor_id', $vendor->id)
            ->where('user_id', $payment->user_id)
            ->exists();
            
        if (!$isStaff) {
            abort(403, 'Unauthorized access to this payment.');
        }
        
        $payment->paid_amount = $request->paid_amount;
        $payment->payment_method = $request->payment_method;
        $payment->transaction_id = $request->transaction_id;
        $payment->payment_date = $request->payment_date ?? Carbon::today();
        $payment->notes = $request->notes;
        $payment->processed_by = Auth::id();
        
        // Recalculate pending amount and status
        $payment->pending_amount = max(0, $payment->net_salary - $payment->paid_amount);
        
        if ($payment->paid_amount >= $payment->net_salary && $payment->net_salary > 0) {
            $payment->status = 'paid';
        } elseif ($payment->paid_amount > 0) {
            $payment->status = 'partial';
        } else {
            $payment->status = 'pending';
        }
        
        $payment->save();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'payment' => $payment,
            ]);
        }
        
        return redirect()->back()->with('success', 'Payment processed successfully.');
    }

    /**
     * Update deductions and bonus for a payment.
     */
    public function updateAdjustments(Request $request, $id)
    {
        $vendor = $this->getVendor();
        
        $request->validate([
            'deductions' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $payment = SalaryPayment::findOrFail($id);
        
        // Verify payment belongs to vendor's staff
        $isStaff = VendorStaff::where('vendor_id', $vendor->id)
            ->where('user_id', $payment->user_id)
            ->exists();
            
        if (!$isStaff) {
            abort(403, 'Unauthorized access to this payment.');
        }
        
        $payment->deductions = $request->deductions ?? 0;
        $payment->bonus = $request->bonus ?? 0;
        
        if ($request->has('notes')) {
            $payment->notes = $request->notes;
        }
        
        // Recalculate net salary
        $payment->net_salary = round($payment->earned_salary - $payment->deductions + $payment->bonus, 2);
        $payment->pending_amount = round(max(0, $payment->net_salary - $payment->paid_amount), 2);
        
        // Update status
        if ($payment->paid_amount >= $payment->net_salary && $payment->net_salary > 0) {
            $payment->status = 'paid';
        } elseif ($payment->paid_amount > 0) {
            $payment->status = 'partial';
        } else {
            $payment->status = 'pending';
        }
        
        $payment->save();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Adjustments updated successfully',
                'payment' => $payment,
            ]);
        }
        
        return redirect()->back()->with('success', 'Adjustments updated successfully.');
    }

    /**
     * Show salary slip for a payment.
     */
    public function slip($id)
    {
        $vendor = $this->getVendor();
        
        $payment = SalaryPayment::with(['user', 'processedBy'])->findOrFail($id);
        
        // Verify payment belongs to vendor's staff
        $isStaff = VendorStaff::where('vendor_id', $vendor->id)
            ->where('user_id', $payment->user_id)
            ->exists();
            
        if (!$isStaff) {
            abort(403, 'Unauthorized access to this payment.');
        }
        
        // Get attendance details
        $attendances = Attendance::forUser($payment->user_id)
                                 ->forMonth($payment->month, $payment->year)
                                 ->orderBy('date')
                                 ->get();
        
        $salaryBreakdown = $payment->getSalaryBreakdown();
        
        return view('vendor.salary.slip', compact('payment', 'attendances', 'salaryBreakdown'));
    }

    /**
     * Delete a salary record.
     */
    public function destroy($id)
    {
        $vendor = $this->getVendor();
        
        $salary = Salary::where('vendor_id', $vendor->id)->findOrFail($id);
        
        // If this was active, activate the previous one
        if ($salary->is_active) {
            $previousSalary = Salary::where('user_id', $salary->user_id)
                                    ->where('vendor_id', $vendor->id)
                                    ->where('id', '!=', $id)
                                    ->orderBy('effective_from', 'desc')
                                    ->first();
            
            if ($previousSalary) {
                $previousSalary->is_active = true;
                $previousSalary->effective_to = null;
                $previousSalary->save();
            }
        }
        
        $salary->delete();
        
        return redirect()->back()->with('success', 'Salary record deleted successfully.');
    }
}