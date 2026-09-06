@extends('layouts.app')

@section('title','Freights')

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">Freights</h3>

        <div class="card-tools">
            <a href="{{ route('freights.create') }}"
               class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> New Freight
            </a>
        </div>
    </div>

    <div class="card-body">

        <form method="GET" class="mb-3">
            <div class="row">

                <div class="col-md-4">
                    <select name="freight_service_id"
                            class="form-control"
                            onchange="this.form.submit()">

                        <option value="">
                            All Freight Services
                        </option>

                        @foreach($services as $service)
                            <option value="{{ $service->id }}"
                                {{ request('freight_service_id') == $service->id ? 'selected' : '' }}>
                                {{ $service->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

            </div>
        </form>

        <table class="table table-bordered table-hover table-erp">

            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Airway No</th>
                    <th>Consignor</th>
                    <th>Service</th>
                    <th class="text-right">USD</th>
                    <th class="text-right">LKR</th>
                    <th class="text-right">Paid</th>
                    <th class="text-right">Balance</th>
                    <th>Status</th>
                    <th width="70">Action</th>
                </tr>
            </thead>

            <tbody>

                @forelse($freights as $freight)

                    <tr>
                        <td>{{ $freight->sale->invoice_id }}</td>

                        <td>
                            {{ \Carbon\Carbon::parse($freight->date)->format('Y-m-d') }}
                        </td>

                        <td>{{ $freight->sale->airway_number }}</td>

                        <td>{{ $freight->sale->consignor }}</td>

                        <td>{{ $freight->service->name }}</td>

                        <td class="text-right">
                            {{ number_format($freight->amount_usd, 2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format($freight->amount_lkr, 2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format($freight->total_paid, 2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format($freight->balance_amount, 2) }}
                        </td>

                        <td>
                            @if($freight->status == 'active')
                                <span class="badge badge-success">
                                    Active
                                </span>
                            @else
                                <span class="badge badge-danger">
                                    Cancelled
                                </span>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('freights.show', $freight->id) }}"
                               class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="11"
                            class="text-center">
                            No freight records found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

        {{ $freights->withQueryString()->links() }}

    </div>
</div>

@endsection