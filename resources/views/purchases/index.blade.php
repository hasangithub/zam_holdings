@extends('layouts.app')

@section('title','Purchases')

@section('content')

<div class="card">
    <div class="card-header">
    <h3 class="card-title">Purchases List</h3>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm float-right">
            + New Purchase
        </a>
    </div>
    <div class="card-body table-responsive">

        <table id="purchasesTable" class="table table-bordered table-sm table-erp">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Supplier</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($purchases as $p)
                <tr>
                    <td>{{ $p->id }}</td>
                    <td>{{ $p->supplier->name }}</td>
                    <td>{{ $p->purchase_date }}</td>
                    <td>{{ $p->total }}</td>
                    <td>
                        <a href="{{ route('purchases.invoice',$p->id) }}" class="btn btn-info btn-xs">Invoice</a>
                        <a href="{{ route('purchases.show',$p->id) }}" class="btn btn-info btn-xs">View</a>
                        <a href="{{ route('purchases.edit',$p->id) }}" class="btn btn-warning btn-xs">Edit</a>

                        <form method="POST" action="{{ route('purchases.destroy',$p->id) }}" style="display:inline;">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-xs">Delete</button>
                        </form>
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
        $('#purchasesTable').DataTable({
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