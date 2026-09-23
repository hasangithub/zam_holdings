@extends('layouts.app')

@section('title', 'Salary Advances')

@section('content')

<div class="container-fluid">

    <div class="card card-primary card-outline">

        <div class="card-header">

            <h3 class="card-title">
                <i class="fas fa-hand-holding-usd"></i>
                Salary Advances
            </h3>

            <div class="card-tools">

                <a href="{{ route('salary-advances.create') }}"
                   class="btn btn-primary btn-sm">

                    <i class="fas fa-plus"></i>
                    New Salary Advance

                </a>

            </div>

        </div>

        <div class="card-body p-2">

            <div class="table-responsive">

                <table class="table table-bordered table-hover table-sm">

                    <thead class="thead-light">

                        <tr>

                            <th>#</th>
                            <th>Date</th>
                            <th>Employee</th>
                            <th class="text-right">
                                Advance
                            </th>

                            <th class="text-right">
                                Returned
                            </th>

                            <th class="text-right">
                                Outstanding
                            </th>

                            <th>Status</th>
                            <th width="80">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($salaryAdvances as $advance)

                            @php
                                $returned = $advance->payments->sum('amount');
                                $outstanding = $advance->amount - $returned;
                            @endphp

                            <tr>

                                <td>
                                    {{ $advance->id }}
                                </td>

                                <td>
                                    {{ $advance->advance_date->format('Y-m-d') }}
                                </td>

                                <td>
                                    {{ $advance->employee->name ?? '-' }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($advance->amount, 2) }}
                                </td>

                                <td class="text-right text-success">
                                    {{ number_format($returned, 2) }}
                                </td>

                                <td class="text-right font-weight-bold">

                                    {{ number_format($outstanding, 2) }}

                                </td>

                                <td>

                                    @if($advance->status == 'pending')

                                        <span class="badge badge-warning">
                                            Pending
                                        </span>

                                    @elseif($advance->status == 'partial')

                                        <span class="badge badge-info">
                                            Partial
                                        </span>

                                    @elseif($advance->status == 'fully_returned')

                                        <span class="badge badge-success">
                                            Fully Returned
                                        </span>

                                    @else

                                        <span class="badge badge-danger">
                                            Cancelled
                                        </span>

                                    @endif

                                </td>

                                <td>

                                    <a href="{{ route(
                                        'salary-advances.show',
                                        $advance
                                    ) }}"
                                       class="btn btn-info btn-xs">

                                        <i class="fas fa-eye"></i>

                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8"
                                    class="text-center text-muted">

                                    No salary advances found.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection