@extends('layouts.app')

@section('title', 'Branch Management')

@section('content')

<div class="card">

    <div class="card-header">

        <h3 class="card-title">
            <i class="fas fa-code-branch mr-1"></i>
            Branch Management
        </h3>

        <div class="card-tools">

            <a href="{{ route('branches.create') }}"
               class="btn btn-primary btn-sm">

                <i class="fas fa-plus"></i>
                Add Branch

            </a>

        </div>

    </div>

    <div class="card-body p-0">

        @if(session('success'))

            <div class="alert alert-success m-3">
                {{ session('success') }}
            </div>

        @endif

        @if(session('error'))

            <div class="alert alert-danger m-3">
                {{ session('error') }}
            </div>

        @endif


        <div class="table-responsive">

            <table class="table table-bordered table-hover table-sm mb-0">

                <thead class="thead-light">

                    <tr>

                        <th width="60">#</th>

                        <th>Branch</th>

                        <th width="100" class="text-center">
                            Users
                        </th>

                        <th width="100" class="text-center">
                            Status
                        </th>

                        <th width="220" class="text-center">
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody>

                @forelse($branches as $branch)

                    <tr>

                        <td>
                            {{ $branch->id }}
                        </td>

                        <td>
                            <strong>
                                {{ $branch->name }}
                            </strong>

                            @if($branch->id == 1)

                                <span class="badge badge-primary ml-1">
                                    Head Office
                                </span>

                            @endif
                        </td>

                        <td class="text-center">

                            <span class="badge badge-info">
                                {{ $branch->users_count }}
                            </span>

                        </td>

                        <td class="text-center">

                            @if($branch->is_active)

                                <span class="badge badge-success">
                                    Active
                                </span>

                            @else

                                <span class="badge badge-danger">
                                    Inactive
                                </span>

                            @endif

                        </td>

                        <td class="text-center">

                            <a href="{{ route('branches.users', $branch) }}"
                               class="btn btn-info btn-xs">

                                <i class="fas fa-users"></i>
                                Users

                            </a>


                            <a href="{{ route('branches.edit', $branch) }}"
                               class="btn btn-warning btn-xs">

                                <i class="fas fa-edit"></i>

                            </a>


                            @if($branch->id != 1)

                                <form
                                    action="{{ route('branches.toggle-status', $branch) }}"
                                    method="POST"
                                    class="d-inline">

                                    @csrf

                                    <button type="submit"
                                            class="btn btn-xs
                                            {{ $branch->is_active
                                                ? 'btn-danger'
                                                : 'btn-success' }}">

                                        @if($branch->is_active)

                                            <i class="fas fa-ban"></i>

                                        @else

                                            <i class="fas fa-check"></i>

                                        @endif

                                    </button>

                                </form>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="5"
                            class="text-center text-muted py-3">

                            No branches found.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection