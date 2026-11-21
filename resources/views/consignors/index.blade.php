
@extends('layouts.app')

@section('title', 'Consignors')

@section('content')
<div class="container-fluid">

    <div class="card card-primary card-outline">

        <div class="card-header">
            <h3 class="card-title">Consignors</h3>

            <div class="card-tools">
                <a href="{{ route('consignors.create') }}"
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Consignor
                </a>
            </div>
        </div>

        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th width="60">#</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th width="130">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($consignors as $key => $consignor)
                            <tr>
                                <td>
                                    {{ $consignors->firstItem() + $key }}
                                </td>

                                <td>{{ $consignor->name }}</td>

                                <td>{{ $consignor->address }}</td>

                                <td>
                                    <a href="{{ route('consignors.edit', $consignor->id) }}"
                                       class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('consignors.destroy', $consignor->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this consignor?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-danger btn-xs">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">
                                    No consignors found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $consignors->links() }}
            </div>

        </div>
    </div>
</div>
@endsection
