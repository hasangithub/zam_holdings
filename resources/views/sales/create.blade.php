@extends('layouts.app')

@section('title','Create Sale')

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Create Sale</h3>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('sales.store') }}">
            @csrf

            {{-- CUSTOMER --}}
            <div class="form-group">
                <label>Customer</label>
                <select name="customer_id" class="form-control" required>
                    <option value="">Select Customer</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <hr>

            {{-- ITEMS --}}
            <table class="table table-bordered table-sm" id="salesTable">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th width="100">Qty</th>
                        <th width="150">Sale Price</th>
                        <th width="80">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>
                            <select name="items[0][group_key]" class="form-control" required>
                                @foreach($stocks as $s)
                                    <option value="{{ $s->item_id }}|{{ $s->price }}">
                                        {{ $s->item_name }}
                                        | Cost: {{ $s->price }}
                                        | Stock: {{ $s->total_qty }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <input type="number" name="items[0][qty]" class="form-control" required>
                        </td>

                        <td>
                            <input type="number" name="items[0][sale_price]" class="form-control">
                        </td>

                        <td>
                            <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <button type="button" id="addRow" class="btn btn-primary btn-sm">
                + Add Row
            </button>

            <button type="submit" class="btn btn-success btn-sm">
                Save Sale
            </button>

        </form>

    </div>
</div>

@endsection


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    let i = 1;

    document.getElementById('addRow').addEventListener('click', function () {

        let row = `
        <tr>
            <td>
                <select name="items[${i}][group_key]" class="form-control" required>
                         <option></option>   
                    @foreach($stocks as $s)
                        <option value="{{ $s->item_id }}|{{ $s->price }}">
                            {{ $s->item_name }} | Cost: {{ $s->price }} | Stock: {{ $s->total_qty }}
                        </option>
                    @endforeach
                </select>
            </td>

            <td>
                <input type="number" name="items[${i}][qty]" class="form-control" required>
            </td>

            <td>
                <input type="number" name="items[${i}][sale_price]" class="form-control" required>
            </td>

            <td>
                <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
            </td>
        </tr>`;

        document.querySelector('#salesTable tbody').insertAdjacentHTML('beforeend', row);
        i++;
    });

    document.addEventListener('click', function(e){
        if(e.target.classList.contains('removeRow')){
            e.target.closest('tr').remove();
        }
    });

});
</script>

@endpush