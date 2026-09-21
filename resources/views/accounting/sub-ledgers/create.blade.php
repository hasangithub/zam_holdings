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
              action="{{ route('accounting.sub-ledgers.store') }}">

            @csrf

            <div class="card-body">

                @include('accounting.sub-ledgers.form')

            </div>

        </form>

    </div>

</div>

</section>

@endsection

@push('scripts')
    <script>
$(document).ready(function () {

    function toggleBranch() {

        let ledgerId = $('#ledger_id').val();

        if (ledgerId == '1') {

            $('#branch_group').show();

            $('#branch_id').prop('required', true);

        } else {

            $('#branch_group').hide();

            $('#branch_id')
                .prop('required', false)
                .val('');

        }
    }

    $('#ledger_id').on('change', function () {
        toggleBranch();
    });

    // Important for edit page
    toggleBranch();

});
</script>
@endpush
