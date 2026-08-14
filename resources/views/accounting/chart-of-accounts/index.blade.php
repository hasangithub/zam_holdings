@extends('layouts.app')

@section('title', 'Chart of Accounts')

@section('content')

<div class="content-header">
    <div class="container-fluid">

        <div class="row mb-2">

            <div class="col-sm-6">
                <h1 class="m-0">
                    Chart of Accounts
                </h1>
            </div>

            <div class="col-sm-6">

                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        Accounting
                    </li>

                    <li class="breadcrumb-item active">
                        Chart of Accounts
                    </li>
                </ol>

            </div>

        </div>

    </div>
</div>


<section class="content">

<div class="container-fluid">

    {{-- Statistics --}}

    <div class="row">

        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-info">

                <div class="inner">

                    <h3>
                        {{ $statistics['account_types'] }}
                    </h3>

                    <p>Account Types</p>

                </div>

                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>

            </div>
        </div>


        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success">

                <div class="inner">

                    <h3>
                        {{ $statistics['account_groups'] }}
                    </h3>

                    <p>Account Groups</p>

                </div>

                <div class="icon">
                    <i class="fas fa-folder"></i>
                </div>

            </div>
        </div>


        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-warning">

                <div class="inner">

                    <h3>
                        {{ $statistics['ledgers'] }}
                    </h3>

                    <p>Ledgers</p>

                </div>

                <div class="icon">
                    <i class="fas fa-book"></i>
                </div>

            </div>
        </div>


        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-primary">

                <div class="inner">

                    <h3>
                        {{ $statistics['sub_ledgers'] }}
                    </h3>

                    <p>Sub Ledgers</p>

                </div>

                <div class="icon">
                    <i class="fas fa-sitemap"></i>
                </div>

            </div>
        </div>

    </div>


    {{-- Header --}}

    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                Account Structure
            </h3>

            <div class="card-tools">

                @can('account_group.create')
                    <a href="{{ route('accounting.account-groups.create') }}"
                       class="btn btn-success btn-sm">

                        <i class="fas fa-plus"></i>
                        Account Group

                    </a>
                @endcan

                @can('ledger.create')
                    <a href="{{ route('accounting.ledgers.create') }}"
                       class="btn btn-primary btn-sm">

                        <i class="fas fa-plus"></i>
                        Ledger

                    </a>
                @endcan

                @can('sub_ledger.create')
                    <a href="{{ route('accounting.sub-ledgers.create') }}"
                       class="btn btn-info btn-sm">

                        <i class="fas fa-plus"></i>
                        Sub Ledger

                    </a>
                @endcan

            </div>

        </div>


        <div class="card-body">

            @foreach($accountTypes as $accountType)

                <div class="card card-outline card-dark">

                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="fas fa-layer-group mr-2"></i>

                            <strong>
                                {{ $accountType->code }}
                                -
                                {{ $accountType->name }}
                            </strong>

                        </h3>

                    </div>


                    <div class="card-body">

                        @foreach($accountType->accountGroups as $group)

                            <div class="mb-3">

                                <div class="font-weight-bold text-primary">

                                    <i class="fas fa-folder mr-2"></i>

                                    {{ $group->code }}
                                    -
                                    {{ $group->name }}

                                </div>


                                @foreach($group->ledgers as $ledger)

                                    <div class="ml-4 mt-2">

                                        <div class="font-weight-bold">

                                            <i class="fas fa-book mr-2"></i>

                                            {{ $ledger->code }}
                                            -
                                            {{ $ledger->name }}

                                        </div>


                                        @foreach($ledger->subLedgers as $subLedger)

                                            <div class="ml-4 mt-1 text-muted">

                                                <i class="fas fa-angle-right mr-2"></i>

                                                {{ $subLedger->code }}
                                                -
                                                {{ $subLedger->name }}

                                            </div>

                                        @endforeach

                                    </div>

                                @endforeach

                            </div>

                        @endforeach

                    </div>

                </div>

            @endforeach

        </div>

    </div>

</div>

</section>

@endsection