@extends('layouts.app')

@section('title','Categories')

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Categories</h3>
        <a href="{{ route('expense-categories.create') }}"
            class="btn btn-primary btn-sm float-right">
            + Add Category
        </a>
    </div>

    <div class="card-body table-responsive">
        <table id="categoriesTable" class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($categories as $cat)
                <tr>
                    <td>{{ $cat->id }}</td>
                    <td>{{ $cat->name }}</td>
                    <td>
                        <a href="/expense-categories/{{ $cat->id }}/edit" class="btn btn-warning btn-xs">Edit</a>
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
        $('#categoriesTable').DataTable({
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