@extends('layouts.app')

@section('title', 'Create Account Group')

@section('content')

<section class="content-header">

    <div class="container-fluid">

        <h1>
            Create Account Group
        </h1>

    </div>

</section>


<section class="content">

<div class="container-fluid">

    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                Account Group Details
            </h3>

        </div>


        <form method="POST"
              action="{{ route('accounting.account-groups.store') }}">

            @csrf

            <div class="card-body">

                @include('accounting.account-groups.form')

            </div>

        </form>

    </div>

</div>

</section>

@endsection