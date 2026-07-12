@extends('layouts.app')

@section('content')

<div class="card">

    <div class="card-header d-flex justify-content-between">

        <h3>
            Customer Statement
            - {{ $customer->name }}
        </h3>

        <button class="btn btn-success"
                data-toggle="modal"
                data-target="#paymentModal">
            Add Payment
        </button>

    </div>

    <div class="card-body">

        <div class="row mb-3">

            <div class="col-md-4">
                <div class="alert alert-info">
                    Total Sales:
                    {{ number_format($totalSales,2) }}
                </div>
            </div>

            <div class="col-md-4">
                <div class="alert alert-success">
                    Total Payments:
                    {{ number_format($totalPayments,2) }}
                </div>
            </div>

            <div class="col-md-4">
                <div class="alert alert-danger">
                    Outstanding:
                    {{ number_format($outstanding,2) }}
                </div>
            </div>

        </div>

        <table class="table table-bordered table-sm">

            <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Reference</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>
            </thead>

            <tbody>

            @foreach($transactions as $row)

                <tr>

                    <td>{{ $row->trans_date }}</td>

                    <td>{{ $row->type }}</td>

                    <td>
                        {{ $row->invoice_id }}
                    </td>

                    <td>
                        {{ number_format($row->debit,2) }}
                    </td>

                    <td>
                        {{ number_format($row->credit,2) }}
                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

    </div>

</div>

@include('customers.payment_modal')

@endsection