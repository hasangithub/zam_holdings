<?php

namespace App\Http\Controllers;

use App\Models\FreightRecord;
use Illuminate\Http\Request;

class FreightRecordController extends Controller
{
    public function index()
    {
        $records = FreightRecord::orderBy('date', 'desc')->get();

        return view('freights.index', compact('records'));
    }

    public function create()
    {
        return view('freights.create');
    }

    public function store(Request $request)
    {

        $request->validate([
            'date' => 'required|date',
            'exchange_rate' => 'required|numeric'
        ]);

        $rate = $request->exchange_rate;

        FreightRecord::create([
            'date' => $request->date,
            'airway_no' => $request->airway_no,
            'consignor' => $request->consignor,
            'consignee' => $request->consignee,

            'net_weight' => $request->net_weight,
            'gross_weight' => $request->gross_weight,
            'boxes' => $request->boxes,

            'exchange_rate' => $rate,

            'custom_usd' => $request->custom_usd,
            'custom_lkr' => $request->custom_usd * $rate,

            'freight_usd' => $request->freight_usd,
            'freight_lkr' => $request->freight_usd * $rate,

            'cusdec_no' => $request->cusdec_no,
            'booking' => $request->booking,
            'bank' => $request->bank,
        ]);

        return redirect()->back()->with('success', 'Saved successfully');
    }

    public function show($id)
    {
        $record = FreightRecord::findOrFail($id);

        return view('freights.show', compact('record'));
    }

    public function destroy(FreightRecord $freightRecord)
    {
        return back();
    }
}
