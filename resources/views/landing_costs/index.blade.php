@extends('layouts.app')

@section('title','Landing Cost Calculation')

@section('content')

<div class="container-fluid">

    <div class="card card-default">

        <div class="card-header">

            <h3 class="card-title">
                Landing Cost Calculation
            </h3>

            <div class="card-tools">

                <a href="{{ route('landing-costs.create') }}"
                   class="btn btn-success btn-sm">

                    <i class="fas fa-plus"></i>

                    Add New

                </a>

            </div>

        </div>

        <div class="card-body table-responsive">

            <table class="table table-bordered table-striped table-sm table-erp">

                <thead>

                <tr>

                    <th>Invoice</th>
                    <th>Date</th>
                    <th>AWB No</th>
                    <th>Consignee</th>
                    <th class="text-right">Net Kg</th>
                    <th class="text-right">Gross Kg</th>
                    <th class="text-right">Air Freight $</th>
                    <th class="text-right">Air Freight LKR</th>
                    <th class="text-right">Logistics</th>
                    <th class="text-right">Freight + Logistics / Kg</th>
                    <th class="text-right">Packing / Kg</th>
                    <th class="text-right">Total Cost / Kg</th>
                    <th width="100">Action</th>

                </tr>

                </thead>

                <tbody>

                @forelse($records as $record)

                    <tr>

                        <td>
                            {{ $record->sale->invoice_id }}
                        </td>

                        <td>
                            {{ optional($record->sale)->sale_date }}
                        </td>

                        <td>
                            {{ optional($record->sale)->airway_no }}
                        </td>

                        <td>
                            {{ optional($record->sale)->consignee }}
                        </td>

                        <td class="text-right">
                            {{ number_format(optional($record->sale->items)->sum('qty') ?? 0,2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format(optional($record)->gross_weight ?? 0,2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format($record->air_freight_usd,2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format($record->air_freight_lkr,2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format($record->logistics_expenses,2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format($record->freight_logistics_perkg,2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format($record->packing_cost_perkg,2) }}
                        </td>

                        <td class="text-right font-weight-bold">
                            {{ number_format($record->total_cost_perkg,2) }}
                        </td>

                        <td>

                            <a href="{{ route('landing-costs.show',$record->id) }}">View</a>

                            <form action="{{ route('landing-costs.destroy',$record->id) }}"
                                  method="POST"
                                  class="d-inline">

                                @csrf
                                @method('DELETE')

                                <button
                                    onclick="return confirm('Delete this record?')"
                                    class="btn btn-danger btn-xs">

                                    <i class="fas fa-trash"></i>

                                </button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="13" class="text-center text-muted">

                            No Records Found

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>
    </div>

</div>

@endsection