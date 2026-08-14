@extends('layouts.app')

@section('title', 'Ledgers')

@section('content')

<section class="content-header">

    <div class="container-fluid">

        <div class="row">

            <div class="col-sm-6">

                <h1>
                    Ledgers
                </h1>

            </div>

            <div class="col-sm-6">

                <ol class="breadcrumb float-sm-right">

                    <li class="breadcrumb-item">
                        Accounting
                    </li>

                    <li class="breadcrumb-item active">
                        Ledgers
                    </li>

                </ol>

            </div>

        </div>

    </div>

</section>


<section class="content">

<div class="container-fluid">

    @include('partials.alerts')


    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                Ledger List
            </h3>

            <div class="card-tools">

               

                    <a href="{{ route('accounting.ledgers.create') }}"
                       class="btn btn-primary btn-sm">

                        <i class="fas fa-plus"></i>
                        Add Ledger

                    </a>

               

            </div>

        </div>


        <div class="card-body table-responsive p-0">

            <table class="table table-bordered table-hover">

                <thead>

                    <tr>

                        <th>Code</th>

                        <th>Ledger</th>

                        <th>Account Group</th>

                        <th>Account Type</th>

                        <th>Status</th>

                        <th width="140">Actions</th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($ledgers as $ledger)

                        <tr>

                            <td>
                                {{ $ledger->code }}
                            </td>

                            <td>
                                {{ $ledger->name }}
                            </td>

                            <td>
                                {{ $ledger->accountGroup->name ?? '-' }}
                            </td>

                            <td>
                                {{ $ledger->accountGroup->accountType->name ?? '-' }}
                            </td>

                            <td>

                                @if($ledger->status)

                                    <span class="badge badge-success">
                                        Active
                                    </span>

                                @else

                                    <span class="badge badge-danger">
                                        Inactive
                                    </span>

                                @endif

                            </td>


                            <td>

                                @can('ledger.edit')

                                    <a href="{{ route('accounting.ledgers.edit', $ledger) }}"
                                       class="btn btn-sm btn-primary">

                                        <i class="fas fa-edit"></i>

                                    </a>

                                @endcan


                                @can('ledger.delete')

                                    <form
                                        action="{{ route('accounting.ledgers.destroy', $ledger) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this ledger?')">

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-sm btn-danger">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </form>

                                @endcan

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6"
                                class="text-center">

                                No ledgers found.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <div class="card-footer">

            {{ $ledgers->links() }}

        </div>

    </div>

</div>

</section>

@endsection