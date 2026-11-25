@extends('layouts.app')

@section('title', 'Cost Management')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Cost Management</h4>

        <a href="{{ route('costs.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Cost
        </a>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">

            <table class="table table-bordered table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="65">#</th>
                        <th>Category</th>
                        <th>Subcategory</th>
                        <th width="75">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($costSubCategories as $costSubCategory)
                    <tr>
                        <td>{{ $costSubCategory->id }}</td>
                        <td>{{ $costSubCategory->category->name ?? '-' }}</td>
                        <td>{{ $costSubCategory->name }}</td>

                        <td>
                            <a href="{{ route('costs.sub-categories.statement', $costSubCategory->id) }}"
                                class="btn btn-sm btn-info"
                                title="View Statement">
                                <i class="fas fa-file-invoice-dollar"></i> Statement
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted p-3">
                            No cost records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>

</div>
@endsection
