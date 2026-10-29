
@extends('layouts.app')

@section('title', 'View Fixed Asset')

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">Fixed Asset Details</h3>

        <div class="card-tools">
            <a href="{{ route('fixed-assets.index') }}"
               class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

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

            <div class="col-md-6">
                <table class="table table-sm table-bordered">
                    <tr>
                        <th width="40%">Asset Code</th>
                        <td>{{ $asset->asset_code }}</td>
                    </tr>
                    <tr>
                        <th>Asset Name</th>
                        <td>{{ $asset->name }}</td>
                    </tr>
                    <tr>
                        <th>Purchase Date</th>
                        <td>{{ $asset->purchase_date->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <th>Supplier</th>
                        <td>{{ $asset->supplier->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Purchase Amount</th>
                        <td class="text-right">
                            {{ number_format($asset->amount, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($asset->status === 'active')
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Cancelled</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ $asset->description ?: '-' }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <table class="table table-sm table-bordered">
                    <tr>
                        <th width="40%">Category Ledger</th>
                        <td>
                            {{ $asset->fixedAssetLedger->name ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Asset Sub-Ledger</th>
                        <td>
                            {{ $asset->assetSubLedger->name ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Opening Journal Entry</th>
                        <td>
                            {{ $asset->opening_journal_entry_id ?? 'Not recorded' }}
                        </td>
                    </tr>
                </table>

                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-balance-scale"></i>
                            Opening Balance
                        </h3>
                    </div>

                    <div class="card-body">

                        @if($asset->opening_journal_entry_id)

                            <div class="alert alert-success mb-0">
                                Opening balance has already been recorded.

                                <br>

                                Journal Entry ID:
                                <strong>
                                    {{ $asset->opening_journal_entry_id }}
                                </strong>
                            </div>

                        @else

                            @if($asset->fixed_asset_ledger_id && $asset->asset_sub_ledger_id)

                                <form method="POST"
                                      action="{{ route('fixed-assets.opening-balance', $asset->id) }}">

                                    @csrf

                                    <div class="form-group">
                                        <label>Opening Balance Date</label>

                                        <input type="date"
                                               name="opening_date"
                                               class="form-control"
                                               value="{{ old('opening_date', $asset->purchase_date->format('Y-m-d')) }}"
                                               required>
                                    </div>

                                    <div class="form-group">
                                        <label>Opening Balance Amount</label>

                                        <input type="number"
                                               name="opening_amount"
                                               class="form-control"
                                               step="0.01"
                                               min="0.01"
                                               value="{{ old('opening_amount') }}"
                                               required>
                                    </div>

                                    <button type="submit"
                                            class="btn btn-primary"
                                            onclick="return confirm('Record the opening balance journal entry for this asset?')">

                                        <i class="fas fa-save"></i>
                                        Save Opening Balance

                                    </button>

                                </form>

                            @else

                                <div class="alert alert-warning mb-0">
                                    The category ledger or asset sub-ledger
                                    has not been saved for this asset.
                                    Update the ledger details before recording
                                    the opening balance.
                                </div>

                            @endif

                        @endif

                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

@endsection
