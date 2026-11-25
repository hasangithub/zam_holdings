
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

    <div class="card card-default">
        <div class="card-body">

            <form method="GET" action="{{ route('costs.index') }}">
                <div class="row">

                    <div class="col-md-2">
                        <label>From Date</label>
                        <input type="date" name="from_date"
                               class="form-control"
                               value="{{ request('from_date') }}">
                    </div>

                    <div class="col-md-2">
                        <label>To Date</label>
                        <input type="date" name="to_date"
                               class="form-control"
                               value="{{ request('to_date') }}">
                    </div>

                    <div class="col-md-3">
                        <label>Category</label>
                        <select name="category_id" id="filter_category"
                                class="form-control">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Subcategory</label>
                        <select name="sub_category_id" id="filter_subcategory"
                                class="form-control">
                            <option value="">All Subcategories</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary btn-sm mr-1">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('costs.index') }}"
                           class="btn btn-secondary btn-sm">Reset</a>
                    </div>

                </div>
            </form>

        </div>
    </div>


    <div class="card">
        <div class="card-body table-responsive p-0">

            <table class="table table-bordered table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="65">#</th>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Subcategory</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right">Balance</th>
                        <th>Status</th>
                        <th width="75">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($costs as $cost)
                        <tr>
                            <td>{{ $cost->id }}</td>
                            <td>{{ $cost->cost_date->format('Y-m-d') }}</td>
                            <td>{{ $cost->category->name ?? '-' }}</td>
                            <td>{{ $cost->subCategory->name ?? '-' }}</td>

                            <td class="text-right">
                                {{ number_format($cost->amount, 2) }}
                            </td>

                            <td class="text-right text-success">
                                {{ number_format($cost->paid_amount, 2) }}
                            </td>

                            <td class="text-right text-danger">
                                {{ number_format($cost->balance, 2) }}
                            </td>

                            <td>
                                @if($cost->payment_status == 'Paid')
                                    <span class="badge badge-success">Paid</span>
                                @elseif($cost->payment_status == 'Partially Paid')
                                    <span class="badge badge-warning">Partially Paid</span>
                                @else
                                    <span class="badge badge-danger">Unpaid</span>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('costs.show', $cost->id) }}"
                                   class="btn btn-info btn-xs">
                                    <i class="fas fa-eye"></i>
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

        <div class="card-footer">
            {{ $costs->links() }}
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
$(function () {

    function loadFilterSubCategories(categoryId, selectedId = null) {

        $('#filter_subcategory').html(
            '<option value="">All Subcategories</option>'
        );

        if (!categoryId) return;

        $.get("{{ url('costs/sub-categories') }}/" + categoryId, function (data) {

            let options = '<option value="">All Subcategories</option>';

            data.forEach(function (item) {
                let selected = selectedId == item.id ? 'selected' : '';
                options += `<option value="${item.id}" ${selected}>${item.name}</option>`;
            });

            $('#filter_subcategory').html(options);
        });
    }

    $('#filter_category').on('change', function () {
        loadFilterSubCategories($(this).val());
    });

    if ($('#filter_category').val()) {
        loadFilterSubCategories(
            $('#filter_category').val(),
            "{{ request('sub_category_id') }}"
        );
    }
});
</script>
@endsection
