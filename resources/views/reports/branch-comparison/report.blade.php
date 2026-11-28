
@extends('layouts.app')

@section('title', 'Branch Comparison Report')

@section('content')

<div class="container-fluid">

    {{-- Report Header --}}
    <div class="card card-outline card-primary shadow-sm">

        <div class="card-header py-2">
            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-balance-scale mr-1"></i>
                        Branch Sales vs Supplier Purchase
                    </h3>
                    <div class="text-muted small mt-1">
                        Item quantity comparison report
                    </div>
                </div>

                <div class="card-tools">
                    <button type="button"
                        class="btn btn-default btn-sm"
                        onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                </div>

            </div>
        </div>

        <div class="card-body p-3">

            {{-- Report Information --}}
            <div class="row mb-3">

                <div class="col-md-6 mb-2">

                    <div class="border rounded h-100">

                        <div class="bg-light border-bottom px-3 py-2">
                            <strong class="text-primary">
                                <i class="fas fa-store mr-1"></i>
                                SALES INFORMATION
                            </strong>
                        </div>

                        <div class="p-3">

                            <table class="table table-sm table-borderless mb-0 info-table">

                                <tr>
                                    <th width="35%">Branch</th>
                                    <td>
                                        <strong>{{ $branch->name }}</strong>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Sales Period</th>
                                    <td>
                                        {{ \Carbon\Carbon::parse($requestData['sales_from'])->format('d-m-Y') }}
                                        <span class="text-muted mx-1">to</span>
                                        {{ \Carbon\Carbon::parse($requestData['sales_to'])->format('d-m-Y') }}
                                    </td>
                                </tr>

                            </table>

                        </div>

                    </div>

                </div>

                <div class="col-md-6 mb-2">

                    <div class="border rounded h-100">

                        <div class="bg-light border-bottom px-3 py-2">
                            <strong class="text-success">
                                <i class="fas fa-truck mr-1"></i>
                                PURCHASE INFORMATION
                            </strong>
                        </div>

                        <div class="p-3">

                            <table class="table table-sm table-borderless mb-0 info-table">

                                <tr>
                                    <th width="35%">Supplier</th>
                                    <td>
                                        <strong>{{ $supplier->name }}</strong>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Purchase Period</th>
                                    <td>
                                        {{ \Carbon\Carbon::parse($requestData['purchase_from'])->format('d-m-Y') }}
                                        <span class="text-muted mx-1">to</span>
                                        {{ \Carbon\Carbon::parse($requestData['purchase_to'])->format('d-m-Y') }}
                                    </td>
                                </tr>

                            </table>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Comparison Table --}}
            <div class="d-flex justify-content-between align-items-center mb-2">

                <h6 class="font-weight-bold mb-0">
                    <i class="fas fa-list mr-1 text-muted"></i>
                    Item-wise Comparison
                </h6>

                <small class="text-muted">
                    Difference = Sales Qty - Purchase Qty
                </small>

            </div>

            <div class="table-responsive">

                <table class="table table-bordered table-sm table-erp comparison-table mb-0">

                    <thead>
                        <tr class="bg-light">

                            <th width="42%">Item Description</th>

                            <th width="19%" class="text-right">
                                Purchase Qty
                            </th>

                            <th width="19%" class="text-right">
                                Sales Qty
                            </th>

                            <th width="20%" class="text-right">
                                Difference
                            </th>

                        </tr>
                    </thead>

                    <tbody>

                        @php
                            $grandPurchase = 0;
                            $grandSales = 0;
                            $grandDifference = 0;
                        @endphp

                        @forelse($report as $category => $rows)

                            @php
                                $categoryPurchase = 0;
                                $categorySales = 0;
                                $categoryDifference = 0;
                            @endphp

                            {{-- Category Heading --}}
                            <tr class="category-row">
                                <td colspan="4">
                                    <i class="fas fa-folder-open mr-1 text-primary"></i>
                                    {{ $category }}
                                </td>
                            </tr>

                            @foreach($rows as $row)

                                @php
                                    $categoryPurchase += $row['purchase_qty'];
                                    $categorySales += $row['sales_qty'];
                                    $categoryDifference += $row['difference_qty'];
                                @endphp

                                <tr>

                                    <td class="pl-4">
                                        {{ $row['item_name'] }}
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($row['purchase_qty'], 3) }}
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($row['sales_qty'], 3) }}
                                    </td>

                                    <td class="text-right font-weight-bold">

                                        @if($row['difference_qty'] > 0)
                                            <span class="text-primary">
                                                +{{ number_format($row['difference_qty'], 3) }}
                                            </span>
                                        @elseif($row['difference_qty'] < 0)
                                            <span class="text-danger">
                                                {{ number_format($row['difference_qty'], 3) }}
                                            </span>
                                        @else
                                            <span class="text-success">
                                                {{ number_format($row['difference_qty'], 3) }}
                                            </span>
                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                            {{-- Category Total --}}
                            <tr class="category-total">

                                <td>
                                    {{ $category }} Total
                                </td>

                                <td class="text-right">
                                    {{ number_format($categoryPurchase, 3) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($categorySales, 3) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($categoryDifference, 3) }}
                                </td>

                            </tr>

                            @php
                                $grandPurchase += $categoryPurchase;
                                $grandSales += $categorySales;
                                $grandDifference += $categoryDifference;
                            @endphp

                        @empty

                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    No matching sales or purchase items found.
                                </td>
                            </tr>

                        @endforelse

                        @if($report->isNotEmpty())

                            {{-- Grand Total --}}
                            <tr class="grand-total">

                                <td>
                                    <strong>GRAND TOTAL</strong>
                                </td>

                                <td class="text-right">
                                    {{ number_format($grandPurchase, 3) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($grandSales, 3) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($grandDifference, 3) }}
                                </td>

                            </tr>

                        @endif

                    </tbody>

                </table>

            </div>

            {{-- Difference Legend --}}
            <div class="d-flex flex-wrap justify-content-end mt-2 small">

                <span class="mr-3">
                    <span class="text-primary font-weight-bold">+ Positive</span>
                    <span class="text-muted">Sales exceed purchases</span>
                </span>

                <span class="mr-3">
                    <span class="text-danger font-weight-bold">- Negative</span>
                    <span class="text-muted">Purchases exceed sales</span>
                </span>

                <span>
                    <span class="text-success font-weight-bold">0.000</span>
                    <span class="text-muted">Equal quantities</span>
                </span>

            </div>

        </div>

    </div>

</div>

@endsection

@push('styles')
<style>

    .comparison-table {
        font-size: 12px;
        color: #343a40;
    }

    .comparison-table th {
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .3px;
        padding: 9px 10px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .comparison-table td {
        padding: 6px 10px;
        vertical-align: middle;
    }

    .comparison-table tbody tr:hover:not(.category-row):not(.category-total):not(.grand-total) {
        background-color: #f8f9fa;
    }

    .category-row td {
        background: #e9f0f8;
        color: #24466b;
        font-weight: 700;
        padding: 7px 10px;
        border-top: 2px solid #ced8e3;
    }

    .category-total td {
        background: #f4f6f9;
        font-weight: 700;
        font-size: 11px;
        border-top: 1px solid #d6dce2;
    }

    .grand-total td {
        background: #343a40;
        color: #fff;
        font-weight: 700;
        padding: 10px;
        font-size: 12px;
    }

    .info-table {
        font-size: 12px;
    }

    .info-table th {
        color: #6c757d;
        font-weight: 600;
    }

    .info-table td {
        color: #343a40;
    }

    @media print {

        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            background: #fff !important;
            font-size: 11px;
        }

        .main-header,
        .main-sidebar,
        .main-footer,
        .card-tools,
        .content-header {
            display: none !important;
        }

        .content-wrapper {
            margin-left: 0 !important;
            min-height: auto !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .card-header {
            border-bottom: 1px solid #ccc !important;
        }

        .comparison-table {
            font-size: 10px;
        }

        .comparison-table thead {
            display: table-header-group;
        }

        .comparison-table tr {
            page-break-inside: avoid;
        }

        .category-row,
        .category-total,
        .grand-total {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

    }

</style>
@endpush
