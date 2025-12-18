<?php

namespace App\Http\Controllers;

use App\Models\Biller;
use Illuminate\Http\Request;

class BillerController extends Controller
{
    public function index(Request $request)
    {
        $query = Biller::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $billers = $query->latest()->paginate($perPage);

        return view('billers.index', compact('billers'));
    }

    public function create()
    {
        return view('billers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email|unique:billers,email',
            'phone'        => 'nullable|string|max:20',
            'address'      => 'nullable|string',
            'city'         => 'nullable|string|max:100',
            'country'      => 'nullable|string|max:100',
            'postal_code'  => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'vat_number'   => 'nullable|string|max:50',
            'status'       => 'required|in:active,inactive',
        ]);

        Biller::create($validated);

        return redirect()->route('billers.index')
            ->with('success', 'Biller created successfully!');
    }

    public function show(Biller $biller)
    {
        return view('billers.show', compact('biller'));
    }

    public function edit(Biller $biller)
    {
        return view('billers.edit', compact('biller'));
    }

    public function update(Request $request, Biller $biller)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email|unique:billers,email,' . $biller->id,
            'phone'        => 'nullable|string|max:20',
            'address'      => 'nullable|string',
            'city'         => 'nullable|string|max:100',
            'country'      => 'nullable|string|max:100',
            'postal_code'  => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'vat_number'   => 'nullable|string|max:50',
            'status'       => 'required|in:active,inactive',
        ]);

        $biller->update($validated);

        return redirect()->route('billers.index')
            ->with('success', 'Biller updated successfully!');
    }

    public function destroy(Biller $biller)
    {
        $biller->delete();

        return redirect()->route('billers.index')
            ->with('success', 'Biller deleted successfully!');
    }
}
