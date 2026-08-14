@extends('layouts.app')

@section('title', 'Create Ledger')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <h1>Create Ledger</h1>
    </div>
</section>

<section class="content">

<div class="container-fluid">

    <div class="card">

        <div class="card-header">
            <h3 class="card-title">
                Ledger Details
            </h3>
        </div>

        <form method="POST"
              action="{{ route('accounting.ledgers.store') }}">

            @csrf

            <div class="card-body">

                @include('accounting.ledgers.form')

            </div>

        </form>

    </div>

</div>

</section>

@endsection