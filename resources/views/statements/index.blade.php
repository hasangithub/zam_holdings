@extends('layouts.app')

@section('title', 'Create Supplier')

@section('content')


@if (session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger">
    Please check the submitted information.
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Statement List</h3>

        @if ((int) auth()->user()->branch_id === 2)
        <div class="card-tools">
            <a href="{{ route('statements.create') }}"
                class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create Statement
            </a>
        </div>
        @endif
    </div>

    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped table-sm">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Reference No.</th>
                    <th>As of Date</th>
                    <th class="text-right">Total Assets</th>
                    <th class="text-right">Total Liabilities</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                    <th style="width: 200px;">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($statements as $statement)
                <tr>
                    <td>
                        {{ $statements->firstItem() + $loop->index }}
                    </td>

                    <td>{{ $statement->reference_no }}</td>

                    <td>
                        {{ \Carbon\Carbon::parse($statement->as_of_date)->format('d-m-Y') }}
                    </td>

                    <td class="text-right">
                        {{ number_format($statement->total_assets, 2) }}
                    </td>

                    <td class="text-right">
                        {{ number_format($statement->total_liabilities, 2) }}
                    </td>

                    <td>
                        @if ($statement->status === 'submitted')
                        <span class="badge badge-success">
                            Submitted to Head Office
                        </span>
                        @else
                        <span class="badge badge-secondary">
                            Draft
                        </span>
                        @endif
                    </td>

                    <td>
                        {{ $statement->submitted_at
                                    ? $statement->submitted_at
                                    : '-' }}
                    </td>

                    <td>
                        <a href="{{ route('statements.show', $statement->id) }}"
                            class="btn btn-info btn-xs">
                            <i class="fas fa-eye"></i> View
                        </a>

                        <a href="{{ route('statements.edit', $statement->id) }}"
                            class="btn btn-warning btn-xs">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">
                        No statements found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($statements->hasPages())
    <div class="card-footer">
        {{ $statements->links() }}
    </div>
    @endif
</div>
@stop