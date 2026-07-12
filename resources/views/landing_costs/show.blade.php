@extends('layouts.app')

@section('title','Landing Cost Details')

@section('content')

<div class="container-fluid">

    <div class="card card-default">

        <div class="card-header">
            <h3 class="card-title">Landing Cost Details</h3>

            <div class="card-tools">
                <a href="{{ route('landing-costs.index') }}" class="btn btn-secondary btn-sm">
                    Back
                </a>
            </div>
        </div>

        <div class="card-body">

            @php
            $sale = $record->sale;
            $freight = $sale->freightRecord ?? null;
            $net = $sale->items->sum('qty');
            @endphp

            {{-- ================= INVOICE SECTION ================= --}}
            <div class="card card-outline card-secondary">

                <div class="card-header">
                    <strong>Invoice Information</strong>
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-3">
                            <label class="text-muted mb-0">Invoice No</label>
                            <div class="font-weight-bold">{{ $sale->invoice_id }}</div>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted mb-0">Date</label>
                            <div>{{ $freight->date ?? '-' }}</div>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted mb-0">AWB No</label>
                            <div>{{ $freight->airway_no ?? '-' }}</div>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted mb-0">Consignee</label>
                            <div>{{ $sale->customer->name ?? '-' }}</div>
                        </div>

                    </div>

                </div>
            </div>


            {{-- ================= WEIGHTS ================= --}}
            <div class="card card-outline card-info mt-3">

                <div class="card-header">
                    <strong>Weight Details</strong>
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-4">
                            <label class="text-muted">Net Weight (Kg)</label>
                            <div class="h5">{{ number_format($net,2) }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="text-muted">Gross Weight (Kg)</label>
                            <div class="h5">{{ number_format($record->gross_weight,2) }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="text-muted">Exchange Rate</label>
                            <div class="h5">{{ $record->exchange_rate }}</div>
                        </div>

                    </div>

                </div>
            </div>


            {{-- ================= COST BREAKDOWN ================= --}}
            <div class="card card-outline card-warning mt-3">

                <div class="card-header">
                    <strong>Cost Breakdown</strong>
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-3">
                            <label class="text-muted">Air Freight ($)</label>
                            <div class="font-weight-bold">
                                {{ number_format($record->air_freight_usd,2) }}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted">Air Freight (LKR)</label>
                            <div class="font-weight-bold">
                                {{ number_format($record->air_freight_lkr,2) }}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted">Logistics Expenses</label>
                            <div>{{ number_format($record->logistics_expenses,2) }}</div>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted">Packing / Kg</label>
                            <div>{{ number_format($record->packing_cost_perkg,2) }}</div>
                        </div>

                    </div>

                </div>
            </div>


            {{-- ================= FINAL RESULT ================= --}}
            <div class="row mt-3">

                <div class="col-md-6">

                    <div class="card bg-light">

                        <div class="card-body text-center">

                            <label class="text-muted">Freight + Logistics / Kg</label>

                            <h4 class="mb-0">
                                {{ number_format($record->freight_logistics_perkg,2) }}
                            </h4>

                        </div>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="card bg-success">

                        <div class="card-body text-center text-white">

                            <label>Total Cost / Kg</label>

                            <h3 class="mb-0">
                                {{ number_format($record->total_cost_perkg,2) }}
                            </h3>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection