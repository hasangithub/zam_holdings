@extends('layouts.app') @section('title', 'Supplier Summary') @section('content') <div class="container-fluid">
    <div class="card card-primary card-outline"> {{-- HEADER --}}
        <div class="card-header py-2">
            <h3 class="card-title"> <i class="fas fa-truck"></i> Supplier Summary </h3>
        </div>
        <div class="card-body p-2"> {{-- FILTER --}}
            <form method="GET" action="{{ route('reports.supplier-summary') }}" class="mb-2">
                <div class="form-row align-items-end">
                    <div class="col-md-3"> <label class="mb-1">Supplier Type</label> <select name="supplier_type" class="form-control form-control-sm">
                            <option value="">All Suppliers</option>
                            <option value="Trading Goods" {{ request('supplier_type') == 'Trading Goods' ? 'selected' : '' }}> Trading Goods </option>
                            <option value="Packing Material" {{ request('supplier_type') == 'Packing Material' ? 'selected' : '' }}> Packing Material </option>
                            <option value="Others" {{ request('supplier_type') == 'Others' ? 'selected' : '' }}> Others </option>
                        </select> </div>
                    <div class="col-md-auto"> <button type="submit" class="btn btn-primary btn-sm"> <i class="fas fa-search"></i> Filter </button> <a href="{{ route('reports.supplier-summary') }}" class="btn btn-secondary btn-sm"> <i class="fas fa-sync"></i> Reset </a> </div>
                </div>
            </form> {{-- SUMMARY --}}
            <div class="row mb-2">
                <div class="col-md-4">
                    <div class="small text-muted"> Suppliers </div> <strong> {{ $suppliers->count() }} </strong>
                </div>
                <div class="col-md-4 text-right">
                    <div class="small text-muted"> Total Purchases </div> <strong> {{ number_format($suppliers->sum('purchase_total'), 2) }} </strong>
                </div>
                <div class="col-md-4 text-right">
                    <div class="small text-muted"> Total Outstanding </div> <strong class="text-danger"> {{ number_format($suppliers->sum('outstanding'), 2) }} </strong>
                </div>
            </div> {{-- TABLE --}}
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:45px;">#</th>
                            <th>Supplier</th>
                            <th style="width:180px;">Type</th>
                            <th class="text-right" style="width:150px;"> Purchases </th>
                            <th class="text-right" style="width:150px;"> Payments </th>
                            <th class="text-right" style="width:160px;"> Outstanding </th>
                            <th style="width:120px;"> Last Purchase </th>
                        </tr>
                    </thead>
                    <tbody> @forelse($suppliers as $supplier) <tr>
                            <td> {{ $loop->iteration }} </td>
                            <td> <strong> {{ $supplier->name }} </strong> </td>
                            <td> @if($supplier->supplier_type == 'Trading Goods') <span class="badge badge-primary"> Trading Goods </span> @elseif($supplier->supplier_type == 'Packing Material') <span class="badge badge-warning"> Packing Material </span> @else <span class="badge badge-secondary"> Others </span> @endif </td>
                            <td class="text-right"> {{ number_format($supplier->purchase_total, 2) }} </td>
                            <td class="text-right"> {{ number_format($supplier->payment_total, 2) }} </td>
                            <td class="text-right"> @if($supplier->outstanding > 0) <strong class="text-danger"> {{ number_format($supplier->outstanding, 2) }} </strong> @elseif($supplier->outstanding < 0) <strong class="text-success"> {{ number_format($supplier->outstanding, 2) }} </strong> @else <span class="text-muted"> 0.00 </span> @endif </td>
                            <td> {{ $supplier->last_purchase ? \Carbon\Carbon::parse($supplier->last_purchase)->format('Y-m-d') : '-' }} </td>
                        </tr> @empty <tr>
                            <td colspan="7" class="text-center text-muted py-3"> No suppliers found. </td>
                        </tr> @endforelse </tbody> {{-- TOTAL --}} @if($suppliers->count()) <tfoot>
                        <tr class="font-weight-bold">
                            <td colspan="3" class="text-right"> Total </td>
                            <td class="text-right"> {{ number_format($suppliers->sum('purchase_total'), 2) }} </td>
                            <td class="text-right"> {{ number_format($suppliers->sum('payment_total'), 2) }} </td>
                            <td class="text-right text-danger"> {{ number_format($suppliers->sum('outstanding'), 2) }} </td>
                            <td></td>
                        </tr>
                    </tfoot> @endif
                </table>
            </div>
        </div>
    </div>
</div> @endsection