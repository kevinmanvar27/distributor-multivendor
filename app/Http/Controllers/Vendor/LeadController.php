<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vendor = $this->getVendor();
        
        $query = Lead::where('vendor_id', $vendor->id);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        
        // Status options for filter dropdown
        $statuses = [
            'new' => 'New',
            'contacted' => 'Contacted',
            'qualified' => 'Qualified',
            'converted' => 'Converted',
            'lost' => 'Lost',
        ];

        return view('vendor.leads.index', compact('leads', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('vendor.leads.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $vendor = $this->getVendor();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'note' => 'nullable|string',
            'status' => 'required|in:new,contacted,qualified,converted,lost',
        ]);

        $validated['vendor_id'] = $vendor->id;
        
        Lead::create($validated);

        return redirect()->route('vendor.leads.index')
            ->with('success', 'Lead created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead)
    {
        $vendor = $this->getVendor();
        
        if ($lead->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this lead.');
        }
        
        return view('vendor.leads.show', compact('lead'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lead $lead)
    {
        $vendor = $this->getVendor();
        
        if ($lead->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this lead.');
        }
        
        return view('vendor.leads.edit', compact('lead'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $vendor = $this->getVendor();
        
        if ($lead->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this lead.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'note' => 'nullable|string',
            'status' => 'required|in:new,contacted,qualified,converted,lost',
        ]);

        $lead->update($validated);

        return redirect()->route('vendor.leads.index')
            ->with('success', 'Lead updated successfully.');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Lead $lead)
    {
        $vendor = $this->getVendor();
        
        if ($lead->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this lead.');
        }
        
        $lead->delete();

        return redirect()->route('vendor.leads.index')
            ->with('success', 'Lead deleted successfully.');
    }

    /**
     * Display a listing of trashed leads.
     */
    public function trashed(Request $request)
    {
        $vendor = $this->getVendor();
        
        $query = Lead::onlyTrashed()->where('vendor_id', $vendor->id);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('deleted_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('deleted_at', '<=', $request->to_date);
        }

        $leads = $query->orderBy('deleted_at', 'desc')->paginate(10)->withQueryString();
        
        $statuses = [
            'new' => 'New',
            'contacted' => 'Contacted',
            'qualified' => 'Qualified',
            'converted' => 'Converted',
            'lost' => 'Lost',
        ];

        return view('vendor.leads.trashed', compact('leads', 'statuses'));
    }

    /**
     * Restore a soft deleted lead.
     */
    public function restore($id)
    {
        $vendor = $this->getVendor();
        
        $lead = Lead::onlyTrashed()->where('vendor_id', $vendor->id)->findOrFail($id);
        $lead->restore();

        return redirect()->route('vendor.leads.trashed')
            ->with('success', 'Lead restored successfully.');
    }

    /**
     * Permanently delete a lead.
     */
    public function forceDelete($id)
    {
        $vendor = $this->getVendor();
        
        $lead = Lead::onlyTrashed()->where('vendor_id', $vendor->id)->findOrFail($id);
        $lead->forceDelete();

        return redirect()->route('vendor.leads.trashed')
            ->with('success', 'Lead permanently deleted.');
    }
}