@extends('layouts.app')

@section('title','Items')

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Items</h3>
        <a href="{{ route('items.create') }}" class="btn btn-primary btn-sm float-right">
            + Add Item
        </a>
    </div>

    <div class="card-body table-responsive">

        <table id="itemsTable" class="table table-bordered table-sm table-erp">
            <thead>
                <tr>
                    <th>ItemCode</th>
                    <th>Category</th>
                    <th>Item Name</th>
                    <th>Type</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->item_code?? '-' }}</td>
                    <td>{{ $item->category->name }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->item_type_name }}</td>
                    <td>
                        <a href="/items/{{ $item->id }}/edit" class="btn btn-warning btn-xs">Edit</a>
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
        $('#itemsTable').DataTable({
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