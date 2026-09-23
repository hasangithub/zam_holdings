@extends('layouts.app')

@section('title', 'Return Salary Advance')

@section('content')

<div class="container-fluid">

    <div class="card card-success card-outline">

        <div class="card-header">

            <h3 class="card-title">
                <i class="fas fa-money-bill-wave"></i>
                Return Salary Advance
            </h3>

        </div>

        <form method="POST"
              action="{{ route(
                  'salary-advances.store-return',
                  $salaryAdvance
              ) }}">

            @csrf

            <div class="card-body">

                <div class="row">

                    <div class="col-md-4">

                        <strong>Employee</strong>

                        <p>
                            {{ $salaryAdvance->employee->name ?? '-' }}
                        </p>

                    </div>

                    <div class="col-md-3">

                        <strong>Original Advance</strong>

                        <p>
                            {{ number_format(
                                $salaryAdvance->amount,
                                2
                            ) }}
                        </p>

                    </div>

                    <div class="col-md-3">

                        <strong>Outstanding</strong>

                        <p class="text-danger font-weight-bold">

                            {{ number_format(
                                $outstanding,
                                2
                            ) }}

                        </p>

                    </div>

                </div>

                <hr>

                <div class="row">

                    {{-- DATE --}}
                    <div class="col-md-3">

                        <div class="form-group">

                            <label>Return Date</label>

                            <input type="date"
                                   name="payment_date"
                                   value="{{ old(
                                       'payment_date',
                                       date('Y-m-d')
                                   ) }}"
                                   class="form-control"
                                   required>

                        </div>

                    </div>


                    {{-- AMOUNT --}}
                    <div class="col-md-3">

                        <div class="form-group">

                            <label>Return Amount</label>

                            <input type="number"
                                   name="amount"
                                   step="0.01"
                                   min="0.01"
                                   max="{{ $outstanding }}"
                                   value="{{ old('amount') }}"
                                   class="form-control text-right"
                                   required>

                        </div>

                    </div>


                    {{-- CASH / BANK --}}
                    <div class="col-md-4">

                        <div class="form-group">

                            <label>Receive Into</label>

                            <select name="payment_sub_ledger_id"
                                    class="form-control"
                                    required>

                                <option value="">
                                    Select Cash / Bank
                                </option>

                                @foreach($paymentAccounts as $account)

                                    <option value="{{ $account->id }}"
                                        {{ old(
                                            'payment_sub_ledger_id'
                                        ) == $account->id
                                            ? 'selected'
                                            : '' }}>

                                        {{ $account->name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>

                    </div>

                </div>


                <div class="row">

                    <div class="col-md-4">

                        <div class="form-group">

                            <label>Reference</label>

                            <input type="text"
                                   name="reference"
                                   value="{{ old('reference') }}"
                                   class="form-control">

                        </div>

                    </div>

                    <div class="col-md-8">

                        <div class="form-group">

                            <label>Note</label>

                            <input type="text"
                                   name="note"
                                   value="{{ old('note') }}"
                                   class="form-control">

                        </div>

                    </div>

                </div>


                <div class="alert alert-info">

                    <strong>Accounting:</strong>

                    Dr Cash / Bank

                    <br>

                    Cr Salary Advance - Employee

                </div>

            </div>

            <div class="card-footer">

                <button type="submit"
                        class="btn btn-success">

                    <i class="fas fa-save"></i>
                    Save Return

                </button>

                <a href="{{ route(
                    'salary-advances.show',
                    $salaryAdvance
                ) }}"
                   class="btn btn-secondary">

                    Cancel

                </a>

            </div>

        </form>

    </div>

</div>

@endsection