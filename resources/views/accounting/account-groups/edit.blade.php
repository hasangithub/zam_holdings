@extends('layouts.app')

@section('title', 'Edit Account Group')

@section('content')

<section class="content-header">

    <div class="container-fluid">

        <h1>
            Edit Account Group
        </h1>

    </div>

</section>


<section class="content">

<div class="container-fluid">

    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                Edit Account Group
            </h3>

        </div>


        <form method="POST"
              action="{{ route('accounting.account-groups.update', $accountGroup) }}">

            @csrf
            @method('PUT')

            <div class="card-body">

                @include('accounting.account-groups.form')

            </div>

        </form>

    </div>

</div>

</section>

@endsection