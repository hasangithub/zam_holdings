@extends('layouts.app')

@section('title','Sales')

@section('content')

<div class="card">
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card-header">
        <h3 class="card-title">Sales List</h3>

        <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm float-right">
            + New Sale
        </a>
    </div>

    <div class="card-body table-responsive">

        <table id="salesTable" class="table table-bordered table-hover table-sm table-erp">

            <thead class="bg-light">
                <tr>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
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
                        @if($s->status === 'posted')
                            <span class="badge badge-success">Posted</span>
                        @elseif($s->status === 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @elseif($s->status === 'cancelled')
                            <span class="badge badge-danger">Cancelled</span>
                        @endif
                    </td>

                    <td>

                        <a href="{{ route('sales.invoice',$s->id) }}" class="btn btn-info btn-xs">
                            Invoice
                        </a>

                        <a href="{{ route('sales.show',$s->id) }}" class="btn btn-info btn-xs">
                            View
                        </a>

                        @if($s->currency === 'USD')

                        <a href="{{ route('export-sales.edit', $s->id) }}"
                            class="btn btn-warning btn-sm">
                            Export Edit
                        </a>

                        @else

                        <a href="{{ route('sales.edit', $s->id) }}"
                            class="btn btn-primary btn-sm">
                            Edit
                        </a>

                        @endif


                        @if($s->status !== 'cancelled')
                        <form action="{{ route('sales.destroy', $s->id) }}"
                            method="POST"
                            class="d-inline"
                            onsubmit="return confirm('Are you sure you want to cancel this sale? This will reverse the journal entries and restore inventory.');">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-ban"></i> Cancel
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach

            </tbody>

        </table>

    </div>

</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#salesTable').DataTable({
            responsive: true,
            pageLength: 10,
            lengthChange: true,
            autoWidth: false,
            ordering: true,
            searching: true
        });
    });
</script>
@endpush