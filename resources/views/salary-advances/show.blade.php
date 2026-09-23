@extends('layouts.app')

@section('title', 'Salary Advance')

@section('content')

<div class="container-fluid">

    <div class="card card-primary card-outline">

        <div class="card-header">

            <h3 class="card-title">
                Salary Advance #{{ $salaryAdvance->id }}
            </h3>

            <div class="card-tools">

                @if($salaryAdvance->status != 'fully_returned'
                    && $salaryAdvance->status != 'cancelled')

                    <a href="{{ route(
                        'salary-advances.return',
                        $salaryAdvance
                    ) }}"
                       class="btn btn-success btn-sm">

                        <i class="fas fa-money-bill-wave"></i>
                        Make Return

                    </a>

                @endif

                <a href="{{ route('salary-advances.index') }}"
                   class="btn btn-secondary btn-sm">

                    Back

                </a>

            </div>

        </div>

        <div class="card-body">

            @php
                $returned = $salaryAdvance->payments->sum('amount');
                $outstanding = $salaryAdvance->amount - $returned;
            @endphp

            <div class="row">

                <div class="col-md-3">

                    <strong>Employee</strong>

                    <div>
                        {{ $salaryAdvance->employee->name ?? '-' }}
                    </div>

                </div>

                <div class="col-md-2">

                    <strong>Date</strong>

                    <div>
                        {{ $salaryAdvance->advance_date->format('Y-m-d') }}
                    </div>

                </div>

                <div class="col-md-2">

                    <strong>Advance</strong>

                    <div class="font-weight-bold">
                        {{ number_format(
                            $salaryAdvance->amount,
                            2
                        ) }}
                    </div>

                </div>

                <div class="col-md-2">

                    <strong>Returned</strong>

                    <div class="text-success font-weight-bold">
                        {{ number_format($returned, 2) }}
                    </div>

                </div>

                <div class="col-md-3">

                    <strong>Outstanding</strong>

                    <div class="text-danger font-weight-bold">
                        {{ number_format($outstanding, 2) }}
                    </div>

                </div>

            </div>

            <hr>

            <h5>Return History</h5>

            <div class="table-responsive">

                <table class="table table-bordered table-sm">

                    <thead class="thead-light">

                        <tr>

                            <th>#</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Received Into</th>
                            <th>Reference</th>
                            <th>Journal</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($salaryAdvance->payments as $payment)

                            <tr>

                                <td>
                                    {{ $loop->iteration }}
                                </td>

                                <td>
                                    {{ $payment->payment_date->format('Y-m-d') }}
                                </td>

                                <td class="text-right font-weight-bold">
                                    {{ number_format(
                                        $payment->amount,
                                        2
                                    ) }}
                                </td>

                                <td>
                                    {{ $payment->paymentSubLedger->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $payment->reference ?: '-' }}
                                </td>

                                <td>
                                    @if($payment->journal_entry_id)
                                        JE #{{ $payment->journal_entry_id }}
                                    @else
                                        -
                                    @endif
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6"
                                    class="text-center text-muted">

                                    No returns recorded.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if($salaryAdvance->description)

                <hr>

                <strong>Description</strong>

                <p class="mb-0">
                    {{ $salaryAdvance->description }}
                </p>

            @endif

        </div>

    </div>

</div>

@endsection