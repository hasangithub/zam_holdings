@extends('layouts.app')

@section('title','Edit Sale')

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            Edit Sale #{{ $sale->id }}
            ({{ $sale->currency }})
        </h3>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('export-sales.update',$sale->id) }}">
            @csrf
            @method('PUT')

            {{-- CUSTOMER --}}
            <div class="form-group">
                <label>Customer</label>
                <select name="customer_id" class="form-control" required>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}"
                        {{ $sale->customer_id == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- CURRENCY INFO --}}
            <div class="row">

                <div class="col-md-3">
                    <label>Customer</label>
                    <select name="customer_id" class="form-control">
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}"
                            {{ $sale->customer_id == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Currency</label>
                    <input type="text"
                        class="form-control"
                        value="{{ $sale->currency }}"
                        readonly>
                </div>

                <div class="col-md-3">
                    <label>Exchange Rate</label>
                    <input type="number"
                        step="0.0001"
                        name="exchange_rate"
                        value="{{ $sale->exchange_rate }}"
                        class="form-control">
                </div>

            </div>

            <hr>

            {{-- ITEMS TABLE --}}
            <table class="table table-bordered" id="salesTable">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th width="80">Qty</th>
                        <th width="120">Base Price</th>

                        {{-- LOCAL --}}
                        <th width="120">Sale Price (LKR)</th>

                        {{-- EXPORT ONLY --}}
                        @if($sale->currency == 'USD')
                        <th width="120">Sale Price (USD)</th>
                        <th width="120">Subtotal (USD)</th>
                        @endif

                        <th width="80">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($sale->items as $key => $si)
                    <tr>

                        {{-- ITEM --}}
                        <td>
                            <select name="items[{{ $key }}][item_id]"
                                class="form-control"
                                required>

                                @foreach($items as $item)
                                <option value="{{ $item->id }}"
                                    {{ $si->item_id == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                                @endforeach

                            </select>
                        </td>

                        {{-- QTY --}}
                        <td>
                            <input type="number"
                                name="items[{{ $key }}][qty]"
                                value="{{ $si->qty }}"
                                class="form-control"
                                required>
                        </td>

                        {{-- BASE PRICE --}}
                        <td>
                            <input type="number"
                                name="items[{{ $key }}][base_price]"
                                value="{{ $si->base_price }}"
                                class="form-control"
                                required>
                        </td>

                        {{-- LOCAL PRICE --}}
                        <td>
                            <input type="number"
                                name="items[{{ $key }}][sale_price]"
                                value="{{ $si->sale_price }}"
                                class="form-control">
                        </td>

                        {{-- EXPORT FIELDS --}}
                        @if($sale->currency == 'USD')
                        <td>
                            <input type="number"
                                step="0.01"
                                name="items[{{ $key }}][sale_price_foreign]"
                                value="{{ $si->sale_price_foreign }}"
                                class="form-control">
                        </td>

                        <td>
                            <input type="number"
                                value="{{ $si->sub_total_foreign }}"
                                class="form-control"
                                readonly>
                        </td>
                        @endif

                        {{-- REMOVE --}}
                        <td>
                            <button type="button"
                                class="btn btn-danger btn-sm removeRow">
                                X
                            </button>
                        </td>

                    </tr>
                    @endforeach

                </tbody>
            </table>

            <button type="button" id="addRow" class="btn btn-primary btn-sm">
                + Add Row
            </button>

            <button type="submit" class="btn btn-success btn-sm">
                Update Sale
            </button>

        </form>

    </div>
</div>

@endsection


@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {

        let i = {
            {
                count($sale - > items)
            }
        };

        document.getElementById('addRow').addEventListener('click', function() {

            let exportMode = "{{ $sale->currency }}" === "USD";

            let row = `
        <tr>

            <td>
                <select name="items[${i}][item_id]" class="form-control" required>
                    <option value="">Select Item</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </td>

            <td>
                <input type="number" name="items[${i}][qty]" class="form-control" required>
            </td>

            <td>
                <input type="number" name="items[${i}][base_price]" class="form-control" required>
            </td>

            <td>
                <input type="number" name="items[${i}][sale_price]" class="form-control">
            </td>

            ${exportMode ? `
            <td>
                <input type="number"
                       step="0.01"
                       name="items[${i}][sale_price_foreign]"
                       class="form-control">
            </td>

            <td>
                <input type="text" class="form-control" readonly>
            </td>
            ` : ''}

            <td>
                <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
            </td>

        </tr>`;

            document.querySelector("#salesTable tbody")
                .insertAdjacentHTML('beforeend', row);

            i++;
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('removeRow')) {
                e.target.closest('tr').remove();
            }
        });

    });
</script>

@endpush