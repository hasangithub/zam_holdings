@extends('layouts.app')

@section('title', 'New Journal Entry')

@section('content')

<section class="content-header">

    <div class="container-fluid">

        <h1>
            New Journal Entry
        </h1>

    </div>

</section>


<section class="content">

<div class="container-fluid">

    @include('partials.alerts')


    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                Manual Journal Entry
            </h3>

        </div>


        <form method="POST"
              action="{{ route('accounting.journal-entries.store') }}">

            @csrf

            <div class="card-body">

                @include(
                    'accounting.journal-entries.form'
                )

            </div>

        </form>

    </div>

</div>

</section>

@endsection