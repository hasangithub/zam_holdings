@extends('layouts.app')

@section('title', 'Sub Ledgers')

@section('content')

<section class="content-header">

    <div class="container-fluid">

        <div class="row">

            <div class="col-sm-6">

                <h1>
                    Sub Ledgers
                </h1>

            </div>

            <div class="col-sm-6">

                <ol class="breadcrumb float-sm-right">

                    <li class="breadcrumb-item">
                        Accounting
                    </li>

                    <li class="breadcrumb-item active">
                        Sub Ledgers
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
                Sub Ledger List
            </h3>

            <div class="card-tools">

               

                    <a href="{{ route('accounting.sub-ledgers.create') }}"
                       class="btn btn-primary btn-sm">

                        <i class="fas fa-plus"></i>
                        Add Sub Ledger

                    </a>

               
            </div>

        </div>


        <div class="card-body table-responsive p-0">

            <table class="table table-bordered table-hover">

                <thead>

                    <tr>

                       

                        <th>Sub Ledger</th>

                        <th>Ledger</th>

                        <th>Account Group</th>

                        <th>Account Type</th>

                       

                        <th width="140">Actions</th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($subLedgers as $subLedger)

                        <tr>

                           
                            <td>
                                {{ $subLedger->name }}
                            </td>

                            <td>
                                {{ $subLedger->ledger->name ?? '-' }}
                            </td>

                            <td>
                                {{ $subLedger->ledger->accountGroup->name ?? '-' }}
                            </td>

                            <td>
                                {{ $subLedger->ledger->accountGroup->accountType->name ?? '-' }}
                            </td>

                           

                            <td>

                                @can('sub_ledger.edit')

                                    <a href="{{ route('accounting.sub-ledgers.edit', $subLedger) }}"
                                       class="btn btn-sm btn-primary">

                                        <i class="fas fa-edit"></i>

                                    </a>

                                @endcan


                                @can('sub_ledger.delete')

                                    <form
                                        action="{{ route('accounting.sub-ledgers.destroy', $subLedger) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this sub ledger?')">

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

                            <td colspan="7"
                                class="text-center">

                                No sub ledgers found.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <div class="card-footer">

            {{ $subLedgers->links() }}

        </div>

    </div>

</div>

</section>

@endsection