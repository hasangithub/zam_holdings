@extends('layouts.app')

@section('title','Sales')

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">Sales List</h3>

        <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm float-right">
            + New Sale
        </a>
    </div>

    <div class="card-body table-responsive">

        <table class="table table-bordered table-hover table-sm">

            <thead class="bg-light">
                <tr>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th width="200">Actions</th>
                </tr>
            </thead>

            <tbody>

                @foreach($sales as $s)
                <tr>
                    <td>
                        {{ $s->invoice_id }}
                    </td>

                    <td>
                        {{ $s->customer->name ?? 'Walk-in' }}
                    </td>

                    <td>
                        {{ date('d M Y', strtotime($s->created_at)) }}
                    </td>

                    <td>
                        Rs {{ number_format($s->total, 2) }}
                    </td>

                    <td>
                        Rs {{ number_format($s->total_paid, 2) }}
                    </td>

                    <td>
                        <span class="text-danger">
                            Rs {{ number_format($s->balance_amount, 2) }}
                        </span>
                    </td>

                    <td>

                        @if($s->payment_status == 'paid')
                        <span class="badge bg-success">Paid</span>

                        @elseif($s->payment_status == 'partial')
                        <span class="badge bg-warning">Partial</span>

                        @else
                        <span class="badge bg-danger">Unpaid</span>
                        @endif

                    </td>

                    <td>

                        <a href="{{ route('sales.invoice',$s->id) }}" class="btn btn-info btn-xs">
                            Invoice
                        </a>

                        <a href="{{ route('sales.show',$s->id) }}" class="btn btn-info btn-xs">
                            View
                        </a>

                        <a href="{{ route('sales.edit',$s->id) }}" class="btn btn-warning btn-xs">
                            Edit
                        </a>

                        <form action="{{ route('sales.destroy',$s->id) }}" method="POST" style="display:inline;">

                            @csrf
                            @method('DELETE')

                            <button class="btn btn-danger btn-xs" onclick="return confirm('Delete this sale?')">
                                Delete
                            </button>

                        </form>

                    </td>
                </tr>
                @endforeach

            </tbody>

        </table>

    </div>

</div>

@endsection