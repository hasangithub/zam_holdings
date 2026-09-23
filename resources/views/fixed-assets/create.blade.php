@extends('layouts.app')

@section('title', 'Add Fixed Asset')

@section('content')

<div class="card card-primary">

    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-building"></i>
            Add Fixed Asset
        </h3>
    </div>

    <form method="POST" action="{{ route('fixed-assets.store') }}">
        @csrf

        <div class="card-body">

            <div class="row">

                <div class="col-md-6">

                    <div class="form-group">
                        <label>Asset Name <span class="text-danger">*</span></label>

                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror"
                               required>

                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <div class="col-md-3">

                    <div class="form-group">
                        <label>Purchase Date <span class="text-danger">*</span></label>

                        <input type="date"
                               name="purchase_date"
                               value="{{ old('purchase_date', date('Y-m-d')) }}"
                               class="form-control"
                               required>
                    </div>

                </div>

                <div class="col-md-3">

                    <div class="form-group">
                        <label>Amount <span class="text-danger">*</span></label>

                        <input type="number"
                               step="0.01"
                               min="0.01"
                               name="amount"
                               value="{{ old('amount') }}"
                               class="form-control"
                               required>
                    </div>

                </div>

            </div>

            <div class="row">

                <div class="col-md-4">

                    <div class="form-group">
                        <label>Supplier <span class="text-danger">*</span></label>

                        <select name="supplier_id"
                                class="form-control"
                                required>

                            <option value="">Select Supplier</option>

                            @foreach($suppliers as $supplier)

                                <option value="{{ $supplier->id }}"
                                    {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>

                                    {{ $supplier->name }}

                                </option>

                            @endforeach

                        </select>
                    </div>

                </div>

                <div class="col-md-4">

                    <div class="form-group">
                        <label>Fixed Asset Ledger <span class="text-danger">*</span></label>

                        <select name="fixed_asset_ledger_id"
                                class="form-control"
                                required>

                            <option value="">Select Ledger</option>

                            @foreach($fixedAssetLedgers as $fixedAssetLedger)

                                <option value="{{ $fixedAssetLedger->id }}"
                                    {{ old('fixed_asset_ledger_id') == $fixedAssetLedger->id ? 'selected' : '' }}>

                                    {{ $fixedAssetLedger->name }}

                                </option>

                            @endforeach

                        </select>
                    </div>

                </div>

                <div class="col-md-4">

                    <div class="form-group">
                        <label>Payment</label>

                        <select name="payment_sub_ledger_id"
                                class="form-control">

                            <option value="">
                                Credit / Pay Later
                            </option>

                            @foreach($cashBooks as $cashBook)

                                <option value="{{ $cashBook->id }}"
                                    {{ old('payment_sub_ledger_id') == $cashBook->id ? 'selected' : '' }}>

                                    {{ $cashBook->name }}

                                </option>

                            @endforeach

                        </select>

                        <small class="text-muted">
                            Leave empty if the asset is purchased on credit.
                        </small>
                    </div>

                </div>

            </div>

            <div class="form-group">

                <label>Description</label>

                <textarea name="description"
                          rows="3"
                          class="form-control">{{ old('description') }}</textarea>

            </div>

            <input type="hidden" name="status" value="active">

        </div>

        <div class="card-footer">

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Save Fixed Asset
            </button>

            <a href="{{ route('fixed-assets.index') }}"
               class="btn btn-secondary">
                Cancel
            </a>

        </div>

    </form>

</div>

@endsection