<?php

namespace App\Http\Controllers;

use App\Models\Consignor;
use App\Models\Sale;
use Illuminate\Http\Request;

class ConsignorController extends Controller
{
    public function index()
    {
        $consignors = Consignor::where(
            'branch_id',
            auth()->user()->branch_id
        )
        ->latest()
        ->paginate(20);

        return view('consignors.index', compact('consignors'));
    }

    public function create()
    {
        return view('consignors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);

        Consignor::create([
            'branch_id' => auth()->user()->branch_id,
            'name' => $request->name,
            'address' => $request->address,
        ]);

        return redirect()
            ->route('consignors.index')
            ->with('success', 'Consignor created successfully.');
    }

    public function edit($id)
    {
        $consignor = Consignor::where(
            'branch_id',
            auth()->user()->branch_id
        )->findOrFail($id);

        return view('consignors.edit', compact('consignor'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);

        $consignor = Consignor::where(
            'branch_id',
            auth()->user()->branch_id
        )->findOrFail($id);

        $consignor->update([
            'name' => $request->name,
            'address' => $request->address,
        ]);

        return redirect()
            ->route('consignors.index')
            ->with('success', 'Consignor updated successfully.');
    }

    public function destroy($id)
    {
        $consignor = Consignor::where(
            'branch_id',
            auth()->user()->branch_id
        )->findOrFail($id);

        if (Sale::where('consignor_id', $consignor->id)->exists()) {
            return redirect()
                ->route('consignors.index')
                ->with('error', 'This consignor is already used in sales and cannot be deleted.');
        }

        $consignor->delete();

        return redirect()
            ->route('consignors.index')
            ->with('success', 'Consignor deleted successfully.');
    }
}
