@extends('layouts.app')

@section('title', 'Edit Ledger')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <h1>Edit Ledger</h1>
    </div>
</section>

<section class="content">

<div class="container-fluid">

    <div class="card">

        <div class="card-header">
            <h3 class="card-title">
                Edit Ledger
            </h3>
        </div>

        <form method="POST"
              action="{{ route('accounting.ledgers.update', $ledger) }}">

            @csrf
            @method('PUT')

            <div class="card-body">

                @include('accounting.ledgers.form')

            </div>

        </form>

    </div>

</div>

</section>

@endsection