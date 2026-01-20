<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Lead::query();

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

        return view('admin.leads.index', compact('leads', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.leads.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'note' => 'nullable|string',
            'status' => 'required|in:new,contacted,qualified,converted,lost',
        ]);

        Lead::create($validated);

        return redirect()->route('admin.leads.index')
            ->with('success', 'Lead created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead)
    {
        return view('admin.leads.show', compact('lead'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lead $lead)
    {
        return view('admin.leads.edit', compact('lead'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'note' => 'nullable|string',
            'status' => 'required|in:new,contacted,qualified,converted,lost',
        ]);

        $lead->update($validated);

        return redirect()->route('admin.leads.index')
            ->with('success', 'Lead updated successfully.');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Lead $lead)
    {
        $lead->delete(); // This performs soft delete due to SoftDeletes trait

        return redirect()->route('admin.leads.index')
            ->with('success', 'Lead deleted successfully.');
    }

    /**
     * Display a listing of trashed leads.
     */
    public function trashed(Request $request)
    {
        $query = Lead::onlyTrashed();

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter (on deleted_at for trashed leads)
        if ($request->filled('from_date')) {
            $query->whereDate('deleted_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('deleted_at', '<=', $request->to_date);
        }

        $leads = $query->orderBy('deleted_at', 'desc')->paginate(10)->withQueryString();
        
        // Status options for filter dropdown
        $statuses = [
            'new' => 'New',
            'contacted' => 'Contacted',
            'qualified' => 'Qualified',
            'converted' => 'Converted',
            'lost' => 'Lost',
        ];

        return view('admin.leads.trashed', compact('leads', 'statuses'));
    }

    /**
     * Restore a soft deleted lead.
     */
    public function restore($id)
    {
        $lead = Lead::onlyTrashed()->findOrFail($id);
        $lead->restore();

        return redirect()->route('admin.leads.trashed')
            ->with('success', 'Lead restored successfully.');
    }

    /**
     * Permanently delete a lead.
     */
    public function forceDelete($id)
    {
        $lead = Lead::onlyTrashed()->findOrFail($id);
        $lead->forceDelete();

        return redirect()->route('admin.leads.trashed')
            ->with('success', 'Lead permanently deleted.');
    }
}
