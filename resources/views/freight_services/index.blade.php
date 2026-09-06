@extends('layouts.app')

@section('title','Freight Services')

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Freight Services</h3>

        <div class="card-tools">
            <a href="{{ route('freight-services.create') }}"
               class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Service
            </a>
        </div>
    </div>

    <div class="card-body">

        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th width="60">#</th>
                    <th>Service</th>
                    <th width="100">Status</th>
                    <th width="80">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($services as $service)
                    <tr>
                        <td>{{ $loop->iteration }}</td>

                        <td>{{ $service->name }}</td>

                        <td>
                            @if($service->active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('freight-services.edit',$service->id) }}"
                               class="btn btn-info btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">
                            No freight services found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</div>

@endsection