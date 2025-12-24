<?php

namespace App\Http\Controllers;

use App\Models\CustomerGroup;
use Illuminate\Http\Request;

class CustomerGroupController extends Controller
{
    public function index()
    {
        $groups = CustomerGroup::withCount('customers')->latest()->paginate(20);
        return view('customer-groups.index', compact('groups'));
    }

    public function create()
    {
        return view('customer-groups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customer_groups,name',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        CustomerGroup::create($validated);

        return redirect()->route('customer-groups.index')
            ->with('success', 'Customer group created successfully!');
    }

    public function edit(CustomerGroup $customerGroup)
    {
        return view('customer-groups.edit', compact('customerGroup'));
    }

    public function update(Request $request, CustomerGroup $customerGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customer_groups,name,' . $customerGroup->id,
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $customerGroup->update($validated);

        return redirect()->route('customer-groups.index')
            ->with('success', 'Customer group updated successfully!');
    }

    public function destroy(CustomerGroup $customerGroup)
    {
        if ($customerGroup->customers()->count() > 0) {
            return back()->with('error', 'Cannot delete group with existing customers!');
        }

        $customerGroup->delete();

        return redirect()->route('customer-groups.index')
            ->with('success', 'Customer group deleted successfully!');
    }
}