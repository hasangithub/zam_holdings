@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header">
            <h3 class="card-title">
                Edit Packing Material Usage
            </h3>
        </div>

        <form method="POST"
              action="{{ route('packing-usages.update', $expense->id) }}">

            @csrf
            @method('PUT')

            <div class="card-body">

                @if($errors->any())

                    <div class="alert alert-danger">

                        <ul class="mb-0">

                            @foreach($errors->all() as $error)

                                <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                    </div>

                @endif

                <div class="row">

                    <div class="col-md-4">

                        <div class="form-group">

                            <label>
                                Expense Date
                            </label>

                            <input type="date"
                                   name="expense_date"
                                   class="form-control"
                                   value="{{ old(
                                       'expense_date',
                                       $expense->expense_date
                                   ) }}"
                                   required>

                        </div>

                    </div>

                    <div class="col-md-8">

                        <div class="form-group">

                            <label>
                                Remarks
                            </label>

                            <input type="text"
                                   name="remarks"
                                   class="form-control"
                                   value="{{ old(
                                       'remarks',
                                       $expense->remarks
                                   ) }}">

                        </div>

                    </div>

                </div>


                <div class="d-flex justify-content-between mb-2">

                    <h5>
                        Packing Materials
                    </h5>

                    <button type="button"
                            class="btn btn-sm btn-primary"
                            id="addRow">

                        <i class="fas fa-plus"></i>
                        Add Item

                    </button>

                </div>


                <div class="table-responsive">

                    <table class="table table-bordered"
                           id="itemsTable">

                        <thead>

                            <tr>

                                <th style="width:60%">
                                    Item
                                </th>

                                <th style="width:25%">
                                    Quantity
                                </th>

                                <th style="width:15%">
                                    Action
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach(
                                old('items', $expense->details)
                                as $index => $detail
                            )

                                @php
                                    $itemId = is_array($detail)
                                        ? $detail['item_id']
                                        : $detail->item_id;

                                    $qty = is_array($detail)
                                        ? $detail['qty']
                                        : $detail->qty;
                                @endphp

                                <tr>

                                    <td>

                                        <select
                                            name="items[{{ $index }}][item_id]"
                                            class="form-control item-select"
                                            required>

                                            <option value="">
                                                Select Item
                                            </option>

                                            @foreach($items as $item)

                                                <option
                                                    value="{{ $item->id }}"
                                                    @selected(
                                                        $item->id == $itemId
                                                    )>

                                                    {{ $item->name }}

                                                </option>

                                            @endforeach

                                        </select>

                                    </td>

                                    <td>

                                        <input
                                            type="number"
                                            step="0.001"
                                            min="0.001"
                                            name="items[{{ $index }}][qty]"
                                            class="form-control"
                                            value="{{ $qty }}"
                                            required>

                                    </td>

                                    <td>

                                        <button
                                            type="button"
                                            class="btn btn-danger btn-sm removeRow">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>


            <div class="card-footer">

                <button type="submit"
                        class="btn btn-success">

                    <i class="fas fa-save"></i>
                    Update

                </button>

                <a href="{{ route('packing-usages.index') }}"
                   class="btn btn-secondary">

                    Cancel

                </a>

            </div>

        </form>

    </div>

</div>

@endsection


@section('scripts')

<script>

let rowIndex =
    document.querySelectorAll('#itemsTable tbody tr').length;


document.getElementById('addRow').addEventListener(
    'click',
    function () {

        const row = `

            <tr>

                <td>

                    <select
                        name="items[${rowIndex}][item_id]"
                        class="form-control"
                        required>

                        <option value="">
                            Select Item
                        </option>

                        @foreach($items as $item)

                            <option value="{{ $item->id }}">
                                {{ addslashes($item->name) }}
                            </option>

                        @endforeach

                    </select>

                </td>

                <td>

                    <input
                        type="number"
                        step="0.001"
                        min="0.001"
                        name="items[${rowIndex}][qty]"
                        class="form-control"
                        required>

                </td>

                <td>

                    <button
                        type="button"
                        class="btn btn-danger btn-sm removeRow">

                        <i class="fas fa-trash"></i>

                    </button>

                </td>

            </tr>

        `;

        document
            .querySelector('#itemsTable tbody')
            .insertAdjacentHTML('beforeend', row);

        rowIndex++;

    }
);


document.addEventListener(
    'click',
    function (e) {

        if (
            e.target.closest('.removeRow')
        ) {

            const rows =
                document.querySelectorAll(
                    '#itemsTable tbody tr'
                );

            if (rows.length <= 1) {
                return;
            }

            e.target
                .closest('tr')
                .remove();
        }

    }
);

</script>

@endsection