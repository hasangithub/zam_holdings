<?php

namespace App\Http\Controllers;

use App\Models\LandingCost;
use App\Models\Sale;
use Illuminate\Http\Request;

class LandingCostController extends Controller
{
    /**
     * List
     */
    public function index()
    {
        $records = LandingCost::with('sale')
            ->latest()
            ->get();

        return view('landing_costs.index', compact('records'));
    }

    /**
     * Create
     */
    public function create()
    {
        $sales = Sale::orderBy('invoice_id')
            ->get();

        return view('landing_costs.create', compact('sales'));
    }

    /**
     * Store
     */
    public function store(Request $request)
    {


        $request->validate([

            // 'invoice_id' => 'required|exists:sales,id',

            'air_freight_rate' => 'required|numeric|min:0',

            'logistics_expenses' => 'required|numeric|min:0',

            'packing_cost_perkg' => 'required|numeric|min:0',

        ]);

        $sale = Sale::with('items')->find($request->invoice_id);

        if (!$sale) {
            return back()->withErrors([
                'invoice_id' => 'Invalid invoice selected'
            ]);
        }

        $grossWeight = $request->gross_weight;
        $netWeight = $sale->items->sum('qty');
        $exchangeRate = $request->exchange_rate;

        $airFreightUsd = $grossWeight * $request->air_freight_rate;

        $airFreightLkr = $airFreightUsd * $exchangeRate;

        $freightLogisticsPerKg = 0;

        if ($netWeight > 0) {

            $freightLogisticsPerKg =
                $airFreightLkr + ($request->logistics_expenses / $netWeight);
        }

        $totalCostPerKg =
            $freightLogisticsPerKg
            + $request->packing_cost_perkg;

        LandingCost::create([

            'invoice_id' => $request->invoice_id,
            'exchange_rate' => $request->exchange_rate,
            'air_freight_rate' => $request->air_freight_rate,
            'gross_weight' => $request->gross_weight,
            'air_freight_usd' => $airFreightUsd,
            'air_freight_lkr' => $airFreightLkr,
            'logistics_expenses' => $request->logistics_expenses,
            'freight_logistics_perkg' => $freightLogisticsPerKg,
            'packing_cost_perkg' => $request->packing_cost_perkg,
            'total_cost_perkg' => $totalCostPerKg,

        ]);

        return redirect()
            ->route('landing-costs.index')
            ->with('success', 'Landing Cost saved successfully.');
    }

    /**
     * Edit
     */
    public function edit($id)
    {
        $landingCost = LandingCost::findOrFail($id);

        $sales = Sale::orderBy('invoice_no')->get();

        return view(
            'landing_cost.edit',
            compact('landingCost', 'sales')
        );
    }

    /**
     * Update
     */
    public function update(Request $request, $id)
    {
        $landingCost = LandingCost::findOrFail($id);

        $request->validate([

            'air_freight_rate' => 'required|numeric',

            'logistics_expenses' => 'required|numeric',

            'packing_cost_perkg' => 'required|numeric',

        ]);

        $sale = Sale::with('freightRecord')
            ->findOrFail($landingCost->invoice_id);

        $grossWeight = $sale->freightRecord->gross_weight;
        $netWeight = $sale->freightRecord->net_weight;
        $exchangeRate = $sale->freightRecord->exchange_rate;

        $airFreightUsd = $grossWeight * $request->air_freight_rate;

        $airFreightLkr = $airFreightUsd * $exchangeRate;

        $freightLogisticsPerKg =
            ($airFreightLkr + $request->logistics_expenses)
            / $netWeight;

        $totalCostPerKg =
            $freightLogisticsPerKg
            + $request->packing_cost_perkg;

        $landingCost->update([

            'air_freight_rate' => $request->air_freight_rate,

            'air_freight_usd' => $airFreightUsd,

            'air_freight_lkr' => $airFreightLkr,

            'logistics_expenses' => $request->logistics_expenses,

            'freight_logistics_perkg' => $freightLogisticsPerKg,

            'packing_cost_perkg' => $request->packing_cost_perkg,

            'total_cost_perkg' => $totalCostPerKg,

        ]);

        return redirect()
            ->route('landing-cost.index')
            ->with('success', 'Landing Cost updated successfully.');
    }

    public function show($id)
    {
        $record = LandingCost::with([
            'sale.customer',
            'sale.items',
        ])->findOrFail($id);

        return view('landing_costs.show', compact('record'));
    }

    public function destroy($id)
    {
        LandingCost::findOrFail($id)->delete();

        return redirect()
            ->back()
            ->with('success', 'Landing Cost deleted successfully.');
    }
}
