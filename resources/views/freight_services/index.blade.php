
@extends('layouts.app')

@section('title', 'Freight Services')

@section('content')

<div class="card card-outline card-primary">
    <div class="card-header py-2">
        <h3 class="card-title">Freight Services</h3>

        <div class="card-tools">
            <a href="{{ route('freight-services.create') }}"
               class="btn btn-primary btn-xs">
                <i class="fas fa-plus"></i> Add Service
            </a>
        </div>
    </div>

    <div class="card-body p-2">

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="45">#</th>
                        <th>Service</th>
                        <th width="90" class="text-center">Status</th>
                        <th width="85" class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($services as $service)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td class="font-weight-medium">
                                {{ $service->name }}
                            </td>

                            <td class="text-center">
                                @if($service->active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>

                            <td class="text-center text-nowrap">
                                <a href="{{ route('freight-services.edit', $service->id) }}"
                                   class="btn btn-info btn-xs"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <a href="{{ route('freight-services.statement', $service->id) }}"
                                   class="btn btn-secondary btn-xs"
                                   title="Statement">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                No freight services found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection
