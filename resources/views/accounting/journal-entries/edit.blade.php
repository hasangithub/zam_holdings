@extends('layouts.app')

@section('title', 'Edit Journal Entry')

@section('content')

<section class="content-header">

    <div class="container-fluid">

        <h1>
            Edit Journal Entry
        </h1>

    </div>

</section>


<section class="content">

<div class="container-fluid">

    @include('partials.alerts')


    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                Edit Journal Entry #{{ $journalEntry->id }}
            </h3>

        </div>


        <form method="POST"
              action="{{ route(
                  'accounting.journal-entries.update',
                  $journalEntry
              ) }}">

            @csrf
            @method('PUT')

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