@extends('layouts.app')

@section('title','Freight Management')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="card card-default">

        <div class="card-header">
            <h3 class="card-title">Freight Records</h3>

            <div class="card-tools">
                <a href="/freight-records/create" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Add New
                </a>
            </div>

        </div>

        <div class="card-body table-responsive">

            <table class="table table-bordered table-striped table-erp">

                <thead class="table-grey">

                    <tr>
                        <th>Date</th>
                        <th>Airway No</th>
                        <th>Consignor</th>
                        <th>Consignee</th>
                        <th>Net Wt</th>
                        <th>Gross Wt</th>
                        <th>Boxes</th>
                        <th>Rate</th>
                        <th>Custom ($)</th>
                        <th>Custom (LKR)</th>
                        <th>Freight ($)</th>
                        <th>Freight (LKR)</th>
                        <th>Actions</th>
                    </tr>

                </thead>

                <tbody>

                    @php
                    $totalCustom = 0;
                    $totalFreight = 0;
                    @endphp

                    @foreach($records as $record)

                    @php
                    $totalCustom += $record->custom_lkr;
                    $totalFreight += $record->freight_lkr;
                    @endphp

                    <tr>
                        <td>{{ $record->date }}</td>
                        <td>{{ $record->airway_no }}</td>
                        <td>{{ \App\Models\Sale::CONSIGNORS[$record->consignor] ?? '-' }}</td>
                        <td>{{ $record->consignee }}</td>

                        <td>{{ $record->net_weight }}</td>
                        <td>{{ $record->gross_weight }}</td>
                        <td>{{ $record->boxes }}</td>

                        <td>{{ $record->exchange_rate }}</td>

                        <td>{{ number_format($record->custom_usd,2) }}</td>
                        <td>{{ number_format($record->custom_lkr,2) }}</td>

                        <td>{{ number_format($record->freight_usd,2) }}</td>
                        <td>{{ number_format($record->freight_lkr,2) }}</td>

                        <td>

                            <a href="{{ route('freight-records.show', $record->id) }}"> View </a>
                            <form method="POST"
                                action="{{ route('freight-records.destroy',$record->id) }}"
                                style="display:inline-block">

                                @csrf
                                @method('DELETE')

                                <button class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this record?')">
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                    @endforeach

                </tbody>
            </table>

        </div>

    </div>

</div>

@endsection