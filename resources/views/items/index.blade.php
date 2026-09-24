@extends('layouts.app')

@section('title','Items')

@section('content')

<div class="card">

    <div class="card-header">

        <h3 class="card-title">
            Items
        </h3>

        <a href="{{ route('items.create') }}"
           class="btn btn-primary btn-sm float-right">

            + Add Item

        </a>

    </div>


    <div class="card-body">

        <div class="table-responsive">

            <table id="itemsTable"
                   class="table table-bordered table-sm table-erp w-100">

                <thead>

                    <tr>

                        <th>
                            Item Code
                        </th>

                        <th>
                            Category
                        </th>

                        <th>
                            Item Name
                        </th>

                        <th>
                            Type
                        </th>

                        <th>
                            Parent
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>

            </table>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>

$(document).ready(function () {

    let table = $('#itemsTable').DataTable({

        processing: true,

        serverSide: true,

        responsive: true,

        pageLength: 10,

        lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ],

        searching: true,

        ordering: true,

        autoWidth: false,

        ajax: {
            url: "{{ route('items.index') }}",
            type: "GET"
        },

        columns: [

            {
                data: 'item_code',
                name: 'item_code',
                defaultContent: '-'
            },

            {
                data: 'category',
                name: 'category'
            },

            {
                data: 'name',
                name: 'name'
            },

            {
                data: 'type',
                name: 'item_type'
            },

            {
                data: 'parent',
                name: 'parent_id',
                orderable: false,
                searchable: false
            },

            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }

        ],

        order: [
            [2, 'asc']
        ]

    });


    /*
    |--------------------------------------------------------------------------
    | Parent Item - TomSelect
    |--------------------------------------------------------------------------
    */

    function initializeParentSelects() {

        $('.parent-item').each(function () {

            let select = this;

            // Don't initialize twice
            if (select.tomselect) {
                return;
            }

            let itemId = $(select).data('id');

            new TomSelect(select, {

                valueField: 'id',

                labelField: 'text',

                searchField: ['text'],

                placeholder: 'Search parent item',

                allowEmptyOption: true,

                maxOptions: 20,

                loadThrottle: 300,

                load: function (query, callback) {

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

                        success: function (response) {

                            callback(response.data);

                        },

                        error: function () {

                            callback();

                        }

                    });

                },

                onChange: function (value) {

                    $.ajax({

                        url: "{{ route('items.parent.update') }}",

                        type: "POST",

                        data: {

                            _token: "{{ csrf_token() }}",

                            item_id: itemId,

                            parent_id: value || null

                        },

                        success: function () {

                            toastr.success(
                                'Parent updated'
                            );

                        },

                        error: function () {

                            toastr.error(
                                'Unable to update parent'
                            );

                        }

                    });

                }

            });

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Initialize TomSelect after every DataTable draw
    |--------------------------------------------------------------------------
    */

    table.on('draw', function () {

        initializeParentSelects();

    });


    /*
    |--------------------------------------------------------------------------
    | Initial draw
    |--------------------------------------------------------------------------
    */

    initializeParentSelects();

});

</script>

@endpush