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
                    <th>Parent</th>
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
                         <select class="form-control form-control-sm parent-item"
                            data-id="{{ $item->id }}"
                            data-parent-id="{{ $item->parent_id }}">

                            @if($item->parent)
                            <option value="{{ $item->parent->id }}" selected>
                                {{ $item->parent->name }}
                            </option>
                            @endif

                        </select>
                    </td>
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

          $('.parent-item').each(function() {

                let select = this;
                let itemId = $(select).data('id');

                new TomSelect(select, {
                    valueField: 'id',
                    labelField: 'text',
                    searchField: ['text'],

                    placeholder: 'Search parent item',
                    allowEmptyOption: true,

                    maxOptions: 20,

                    loadThrottle: 300,

                    load: function(query, callback) {

                        if (query.length < 2) {
                            callback();
                            return;
                        }

                        $.ajax({
                            url: "{{ route('items.parent.search') }}",
                            type: "GET",
                            dataType: "json",

                            data: {
                                search: query,
                                item_id: itemId
                            },

                            success: function(response) {
                                callback(response.data);
                            },

                            error: function() {
                                callback();
                            }
                        });

                    },

                    onChange: function(value) {

                        $.ajax({
                            url: "{{ route('items.parent.update') }}",
                            type: "POST",

                            data: {
                                _token: "{{ csrf_token() }}",
                                item_id: itemId,
                                parent_id: value || null
                            },

                            success: function() {
                                toastr.success('Parent updated');
                            },

                            error: function() {
                                toastr.error('Unable to update parent');
                            }
                        });

                    }

                });

            });

    });
</script>
@endpush