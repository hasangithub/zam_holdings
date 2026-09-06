@extends('layouts.app')

@section('title', 'Customer Summary Report')

@section('content')

<div class="container-fluid">

    <div class="card card-primary card-outline">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users"></i> Customer Summary Report
            </h3>
        </div>

        <div class="card-body">

            {{-- FILTER --}}
            <form method="GET" action="{{ route('reports.customer-summary') }}" class="mb-3">

                <div class="row">

                    <div class="col-md-4">
                        <label>Customer Type</label>

                        <select name="type" class="form-control">
                            <option value="">All Customers</option>

                            <option value="local"
                                {{ request('type') == 'local' ? 'selected' : '' }}>
                                Local
                            </option>

                            <option value="export"
                                {{ request('type') == 'export' ? 'selected' : '' }}>
                                Export
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Filter
                        </button>

                        <a href="{{ route('reports.customer-summary') }}"
                           class="btn btn-secondary">
                            Reset
                        </a>
                    </div>

                </div>

            </form>

            <div class="table-responsive">

                <table class="table table-bordered table-hover table-sm">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th class="text-right">Total Sales</th>
                            <th class="text-right">Paid</th>
                            <th class="text-right">Outstanding</th>
                            <th>Last Sale</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($customers as $customer)

                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    {{ $customer->name }}
                                </td>

                                <td>
                                    @if($customer->customer_type == 'export')
                                        <span class="badge badge-info">Export</span>
                                    @else
                                        <span class="badge badge-success">Local</span>
                                    @endif
                                </td>

                                <td class="text-right">
                                    {{ number_format($customer->total_sales ?? 0, 2) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($customer->total_paid ?? 0, 2) }}
                                </td>

                                <td class="text-right font-weight-bold">
                                    {{ number_format($customer->outstanding ?? 0, 2) }}
                                </td>

                                <td>
                                    {{ $customer->last_sale_date
                                        ? \Carbon\Carbon::parse($customer->last_sale_date)->format('Y-m-d')
                                        : '-' }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="text-center">
                                    No customers found
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                    <tfoot>
                        <tr class="font-weight-bold">

                            <td colspan="3" class="text-right">
                                Total
                            </td>

                            <td class="text-right">
                                {{ number_format($customers->sum('total_sales'), 2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($customers->sum('total_paid'), 2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($customers->sum('outstanding'), 2) }}
                            </td>

                            <td></td>

                        </tr>
                    </tfoot>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection