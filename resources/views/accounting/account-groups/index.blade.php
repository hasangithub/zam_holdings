@extends('layouts.app')

@section('title', 'Account Groups')

@section('content')

<div class="content-header">

    <div class="container-fluid">

        <div class="row">

            <div class="col-sm-6">

                <h1 class="m-0">
                    Account Groups
                </h1>

            </div>

            <div class="col-sm-6">

                <ol class="breadcrumb float-sm-right">

                    <li class="breadcrumb-item">
                        Accounting
                    </li>

                    <li class="breadcrumb-item active">
                        Account Groups
                    </li>

                </ol>

            </div>

        </div>

    </div>

</div>


<section class="content">

<div class="container-fluid">

    @include('partials.alerts')


    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                Account Groups
            </h3>

            <div class="card-tools">

              

                    <a href="{{ route('accounting.account-groups.create') }}"
                       class="btn btn-primary btn-sm">

                        <i class="fas fa-plus"></i>
                        Add Account Group

                    </a>

          

            </div>

        </div>


        <div class="card-body table-responsive p-0">

            <table class="table table-hover table-bordered">

                <thead>

                    <tr>

                        <th width="100">
                            Code
                        </th>

                        <th>
                            Account Type
                        </th>

                        <th>
                            Account Group
                        </th>

                      

                        <th width="150">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($accountGroups as $group)

                        <tr>

                            <td>
                                {{ $group->code }}
                            </td>

                            <td>
                                {{ $group->accountType->name ?? '-' }}
                            </td>

                            <td>
                                {{ $group->name }}
                            </td>

                            <td>

                                @can('account_group.edit')

                                    <a href="{{ route('accounting.account-groups.edit', $group) }}"
                                       class="btn btn-sm btn-primary">

                                        <i class="fas fa-edit"></i>

                                    </a>

                                @endcan


                                @can('account_group.delete')

                                    <form
                                        action="{{ route('accounting.account-groups.destroy', $group) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this account group?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-sm btn-danger">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </form>

                                @endcan

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5"
                                class="text-center">

                                No account groups found.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <div class="card-footer">

            {{ $accountGroups->links() }}

        </div>

    </div>

</div>

</section>

@endsection