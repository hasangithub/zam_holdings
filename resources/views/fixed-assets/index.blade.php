@extends('layouts.app')

@section('title', 'Fixed Assets')

@section('content')

<div class="card">

    <div class="card-header">

        <h3 class="card-title">
            Fixed Assets
        </h3>

        <div class="card-tools">

            <a href="{{ route('fixed-assets.create') }}"
               class="btn btn-primary btn-sm">

                <i class="fas fa-plus"></i>
                Add Fixed Asset

            </a>

        </div>

    </div>

    <div class="card-body p-0">

        <table class="table table-sm table-bordered table-hover mb-0">

            <thead>
                <tr>
                    <th>Code</th>
                    <th>Asset</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th class="text-right">Amount</th>
                    <th>Status</th>
                    <th width="120">Action</th>
                </tr>
            </thead>

            <tbody>

                @forelse($assets as $asset)

                    <tr>

                        <td>{{ $asset->asset_code }}</td>

                        <td>{{ $asset->name }}</td>

                        <td>
                            {{ $asset->purchase_date->format('Y-m-d') }}
                        </td>

                        <td>
                            {{ $asset->supplier->name }}
                        </td>

                        <td class="text-right">
                            {{ number_format($asset->amount, 2) }}
                        </td>

                        <td>

                            @if($asset->status === 'active')

                                <span class="badge badge-success">
                                    Active
                                </span>

                            @else

                                <span class="badge badge-danger">
                                    Cancelled
                                </span>

                            @endif

                        </td>

                        <td>

                          

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="text-center">
                            No fixed assets found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>
</div>

@endsection