
@extends('layouts.app')

@section('title', 'Invoice Company Profiles')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Invoice Company Profiles</h3>

        <div class="card-tools">
            <a href="{{ route('invoice-company-profiles.create') }}"
               class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Profile
            </a>
        </div>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Title</th>
                        <th>Sub Title 1</th>
                        <th>Sub Title 2</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profiles as $profile)
                        <tr>
                            <td>{{ $profiles->firstItem() + $loop->index }}</td>
                            <td>{{ $profile->title }}</td>
                            <td>{{ $profile->sub_title_1 ?: '-' }}</td>
                            <td>{{ $profile->sub_title_2 ?: '-' }}</td>
                            <td>{{ $profile->address ?: '-' }}</td>
                            <td>
                                @if($profile->status)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('invoice-company-profiles.edit', $profile->id) }}"
                                   class="btn btn-warning btn-xs">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                No invoice company profiles found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $profiles->links() }}
    </div>
</div>
@endsection
