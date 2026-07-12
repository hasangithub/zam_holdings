@extends('layouts.app')

@section('title','Purchase Inventories')

@section('content')

<div class="card">

    <div class="card-header">

        <h3 class="card-title">Purchase Inventories</h3>

        <div class="card-tools">
            <a href="{{ route('purchase-inventories.create') }}"
               class="btn btn-primary btn-sm">
                Add Purchase
            </a>
        </div>

    </div>

    <div class="card-body">

        <table class="table table-bordered table-striped table-erp">

            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th width="180">Action</th>
                </tr>
            </thead>

            <tbody>

                @forelse($purchases as $purchase)

                    <tr>

                        <td>{{ $purchase->id }}</td>

                        <td>
                            {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y') }}
                        </td>

                        <td>
                            {{ $purchase->supplier->name ?? '' }}
                        </td>

                        <td class="text-right">
                            {{ number_format($purchase->total, 2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format($purchase->paid_amount, 2) }}
                        </td>

                        <td class="text-right">
                            {{ number_format($purchase->balance_amount, 2) }}
                        </td>

                        <td>

                            @if($purchase->payment_status == 'paid')
                                <span class="badge badge-success">
                                    Paid
                                </span>

                            @elseif($purchase->payment_status == 'partial')
                                <span class="badge badge-warning">
                                    Partial
                                </span>

                            @else
                                <span class="badge badge-danger">
                                    Unpaid
                                </span>
                            @endif

                        </td>

                        <td>

                            <a href="{{ route('purchase-inventories.show',$purchase->id) }}"
                               class="btn btn-info btn-sm">
                                View
                            </a>

                            <a href="{{ route('purchase-inventories.edit',$purchase->id) }}"
                               class="btn btn-primary btn-sm">
                                Edit
                            </a>

                            <form method="POST"
                                  action="{{ route('purchase-inventories.destroy',$purchase->id) }}"
                                  style="display:inline-block">

                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Delete this purchase?')">
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8" class="text-center">
                            No Records Found
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="card-footer clearfix">
        {{ $purchases->links() }}
    </div>

</div>

@endsection