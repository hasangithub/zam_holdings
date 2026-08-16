@extends('layouts.app')

@section('title','Packing Usages')

@section('content')

<div class="container-fluid">

    <div class="row">

        {{-- LEFT SIDE --}}
        <div class="col-md-9">

            <div class="card card-primary card-outline">

                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart"></i> Create Packing Usages
                    </h3>
                </div>

                <div class="card-body">

                    @if($errors->any())

                    <div class="alert alert-danger alert-dismissible fade show" role="alert">

                        <strong>
                            Please correct the following errors:
                        </strong>

                        <ul class="mb-0 mt-2">

                            @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                            @endforeach

                        </ul>

                        <button type="button"
                            class="close"
                            data-dismiss="alert"
                            aria-label="Close">

                            <span aria-hidden="true">&times;</span>

                        </button>

                    </div>

                    @endif

                    <form method="POST" action="{{ route('packing-usages.store') }}"> @csrf {{-- SUMMARY CARDS --}}
                        <div class="row mb-3">
                            <div class="col-md-5"> <label>Date</label> <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" class="form-control" required> </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="expenseTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="40%">Item</th>
                                        <th width="15%">Qty</th>
                                        <th width="15%">Price</th>
                                        <th width="20%">Total</th>
                                        <th width="10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td> <select name="items[0][item_id]" class="form-control">
                                                <option value="">Select Item</option> @foreach($items as $item) <option value="{{ $item->id }}"> {{ $item->name }} </option> @endforeach
                                            </select> </td>
                                        <td> <input type="number" step="0.01" name="items[0][qty]" class="form-control qty"> </td>
                                        <td> <input type="number" step="0.01" name="items[0][price]" class="form-control price"> </td>
                                        <td> <input type="text" class="form-control total bg-light" readonly> </td>
                                        <td class="text-center"> <button type="button" class="btn btn-danger btn-sm removeRow">
                                                X
                                            </button> </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> {{-- ACTION BUTTONS --}}
                        <div class="d-flex justify-content-between mt-3"> <button type="button" id="addRow" class="btn btn-primary"> <i class="fas fa-plus"></i> Add Item </button> <button type="submit" class="btn btn-success"> <i class="fas fa-save"></i> Save Usage </button> </div>
                    </form>
                </div>
            </div>

        </div>

        {{-- RIGHT SIDE SUMMARY --}}
        <div class="col-md-3">

            <div class="card card-success card-outline">

                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator"></i> Usage Summary
                    </h5>
                </div>

                <div class="card-body p-2">
                    <div class="p-2 border rounded bg-success text-white">
                        <small>Total Amount</small>
                        <h4 class="mb-0" id="grandTotal">Rs 0.00</h4>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {

        let i = 1;

        document.getElementById('addRow').addEventListener('click', function() {

            let row = `
    <tr>

        <td>
            <select name="items[${i}][item_id]" class="form-control item-select">
                <option value="">Select Item</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}">
                        {{ $item->name }}
                    </option>
                @endforeach
            </select>
        </td>

        <td>
            <input type="number" name="items[${i}][qty]" class="form-control qty">
        </td>

        <td>
            <input type="number" name="items[${i}][price]" class="form-control price">
        </td>

        <td>
            <input type="text" class="form-control total" readonly>
        </td>

        <td>
            <button type="button" class="btn btn-danger btn-sm removeRow">
    X
</button>
        </td>

    </tr>`;

            document.querySelector('#expenseTable tbody')
                .insertAdjacentHTML('beforeend', row);

            i++;
        });

        // REMOVE ROW
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('removeRow')) {
                e.target.closest('tr').remove();
                calcTotal();
            }
        });

        // CALCULATION
        document.addEventListener('input', function(e) {

            if (e.target.classList.contains('qty') || e.target.classList.contains('price')) {
                let row = e.target.closest('tr');

                let qty = parseFloat(row.querySelector('.qty').value || 0);
                let price = parseFloat(row.querySelector('.price').value || 0);

                let total = qty * price;

                row.querySelector('.total').value = total.toFixed(2);

                calcTotal();
            }

        });

        function calcTotal() {
            let sum = 0;

            document.querySelectorAll('.total').forEach(function(el) {
                sum += parseFloat(el.value || 0);
            });
            document.getElementById('grandTotal').innerText = sum.toFixed(2);
        }

    });
</script>

@endpush