<?php

namespace App\Http\Controllers;

use App\Models\FreightService;
use App\Models\SubLedger;
use Illuminate\Http\Request;

class FreightServiceController extends Controller
{
    public function index()
    {
        $services = FreightService::latest()->get();

        return view('freight_services.index', compact('services'));
    }

    public function create()
    {
        return view('freight_services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $frightService = FreightService::create([
            'name' => $request->name,
            'active' => $request->boolean('active'),
        ]);

            $subLedger = SubLedger::create([
                'ledger_id' => 5,
                'name'      => $frightService->name,
            ]);

            $frightService->update(['liability_sub_ledger_id' => $subLedger->id]);

        return redirect()
            ->route('freight-services.index')
            ->with('success', 'Freight service created successfully.');
    }

    public function edit($id)
    {
        $service = FreightService::findOrFail($id);

        return view('freight_services.edit', compact('service'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $service = FreightService::findOrFail($id);

        $service->update([
            'name' => $request->name,
            'active' => $request->boolean('active'),
        ]);

        return redirect()
            ->route('freight-services.index')
            ->with('success', 'Freight service updated successfully.');
    }
}
