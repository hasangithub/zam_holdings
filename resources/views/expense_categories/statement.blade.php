@extends('layouts.app')

@section('title','Categories')

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <strong>Category:</strong> {{ $category->name }}
        </h3>
    </div>

    <div class="card-body">

        <div class="row mb-3">
            <div class="col-md-4">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h4>
                            {{ number_format($statement->sum('debit'), 2) }}
                        </h4>
                        <p>Total Debit</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h4>
                            {{ number_format($statement->sum('credit'), 2) }}
                        </h4>
                        <p>Total Credit / Paid</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h4>
                            {{ number_format($statement->last()['balance'] ?? 0, 2) }}
                        </h4>
                        <p>Outstanding Balance</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 130px;">Date</th>
                        <th>Description</th>
                        <th class="text-right" style="width: 150px;">Debit</th>
                        <th class="text-right" style="width: 150px;">Credit</th>
                        <th class="text-right" style="width: 170px;">Balance</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($statement as $row)
                        <tr>
                            <td>
                                {{ $row['date']
                                    ? \Carbon\Carbon::parse($row['date'])->format('d-m-Y')
                                    : '-' }}
                            </td>

                            <td>{{ $row['description'] }}</td>

                            <td class="text-right">
                                @if($row['debit'] > 0)
                                    {{ number_format($row['debit'], 2) }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="text-right">
                                @if($row['credit'] > 0)
                                    {{ number_format($row['credit'], 2) }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="text-right font-weight-bold">
                                {{ number_format($row['balance'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">
                                No expenses found for this category.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($statement->isNotEmpty())
                    <tfoot class="font-weight-bold">
                        <tr>
                            <td colspan="2" class="text-right">Total</td>

                            <td class="text-right">
                                {{ number_format($statement->sum('debit'), 2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($statement->sum('credit'), 2) }}
                            </td>

                            <td class="text-right">
                                {{ number_format($statement->last()['balance'], 2) }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

    </div>
</div>

@stop
