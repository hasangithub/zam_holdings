@extends('layouts.app')

@section('title','Freight Record Details')

@section('content')

<div class="container-fluid">

    <div class="card card-default">

        <div class="card-header">
            <h3 class="card-title">Freight Record Details</h3>

            <div class="card-tools">
                <a href="{{ route('freight-records.index') }}"
                   class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="card-body">

            {{-- Shipment Information --}}
            <div class="card card-outline card-primary">

                <div class="card-header">
                    <h3 class="card-title">Shipment Information</h3>
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-3">
                            <label class="text-muted">Date</label>
                            <div class="font-weight-bold">{{ $record->date }}</div>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted">Airway No</label>
                            <div class="font-weight-bold">{{ $record->airway_no }}</div>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted">Consignor</label>
                            <div>{{ $record->consignor }}</div>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted">Consignee</label>
                            <div>{{ $record->consignee }}</div>
                        </div>

                    </div>

                    <hr>

                    <div class="row">

                        <div class="col-md-3">
                            <label class="text-muted">Net Weight (Kg)</label>
                            <div>{{ number_format($record->net_weight, 2) }}</div>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted">Gross Weight (Kg)</label>
                            <div>{{ number_format($record->gross_weight, 2) }}</div>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted">Boxes</label>
                            <div>{{ $record->boxes }}</div>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted">Exchange Rate</label>
                            <div>{{ number_format($record->exchange_rate, 4) }}</div>
                        </div>

                    </div>

                </div>

            </div>

            {{-- Invoice Details --}}
            <div class="card card-outline card-success mt-3">

                <div class="card-header">
                    <h3 class="card-title">Invoice Details</h3>
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-3">
                            <label class="text-muted">Custom Invoice (USD)</label>
                            <div>{{ number_format($record->custom_usd, 2) }}</div>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted">Custom Invoice (LKR)</label>
                            <div class="font-weight-bold">
                                {{ number_format($record->custom_lkr, 2) }}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted">Freight Invoice (USD)</label>
                            <div>{{ number_format($record->freight_usd, 2) }}</div>
                        </div>

                        <div class="col-md-3">
                            <label class="text-muted">Freight Invoice (LKR)</label>
                            <div class="font-weight-bold">
                                {{ number_format($record->freight_lkr, 2) }}
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            {{-- Other Information --}}
            <div class="card card-outline card-warning mt-3">

                <div class="card-header">
                    <h3 class="card-title">Other Information</h3>
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-4">
                            <label class="text-muted">CUSDEC No</label>
                            <div>{{ $record->cusdec_no }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="text-muted">Booking</label>
                            <div>{{ $record->booking }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="text-muted">Bank</label>
                            <div>{{ $record->bank }}</div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection