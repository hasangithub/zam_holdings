@extends('layouts.app')

@section('title', 'Create Salary Advance')

@section('content')

<div class="container-fluid">

    <div class="card card-primary card-outline">

        <div class="card-header">

            <h3 class="card-title">
                <i class="fas fa-hand-holding-usd"></i>
                Create Salary Advance
            </h3>

        </div>

        <form method="POST"
              action="{{ route('salary-advances.store') }}">

            @csrf

            <div class="card-body">

                @if($errors->any())

                    <div class="alert alert-danger">

                        <ul class="mb-0">

                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach

                        </ul>

                    </div>

                @endif


                <div class="row">

                    {{-- DATE --}}
                    <div class="col-md-3">

                        <div class="form-group">

                            <label>Advance Date</label>

                            <input type="date"
                                   name="advance_date"
                                   value="{{ old(
                                       'advance_date',
                                       date('Y-m-d')
                                   ) }}"
                                   class="form-control"
                                   required>

                        </div>

                    </div>


                    {{-- EMPLOYEE --}}
                    <div class="col-md-5">

                        <div class="form-group">

                            <label>Employee</label>

                            <select name="employee_id"
                                    class="form-control"
                                    required>

                                <option value="">
                                    Select Employee
                                </option>

                                @foreach($employees as $employee)

                                    <option value="{{ $employee->id }}"
                                        {{ old('employee_id') == $employee->id
                                            ? 'selected'
                                            : '' }}>

                                        {{ $employee->name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>

                    </div>


                    {{-- AMOUNT --}}
                    <div class="col-md-4">

                        <div class="form-group">

                            <label>Advance Amount</label>

                            <input type="number"
                                   name="amount"
                                   step="0.01"
                                   min="0.01"
                                   value="{{ old('amount') }}"
                                   class="form-control text-right"
                                   required>

                        </div>

                    </div>

                </div>


                <div class="row">

                    {{-- PAYMENT ACCOUNT --}}
                    <div class="col-md-5">

                        <div class="form-group">

                            <label>Pay From</label>

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


                    {{-- DESCRIPTION --}}
                    <div class="col-md-7">

                        <div class="form-group">

                            <label>Description</label>

                            <textarea name="description"
                                      rows="2"
                                      class="form-control">{{ old('description') }}</textarea>

                        </div>

                    </div>

                </div>


                <div class="alert alert-info">

                    <strong>Accounting:</strong>

                    Dr Salary Advance - Employee

                    <br>

                    Cr Cash / Bank

                </div>

            </div>

            <div class="card-footer">

                <button type="submit"
                        class="btn btn-primary">

                    <i class="fas fa-save"></i>
                    Save Advance

                </button>

                <a href="{{ route('salary-advances.index') }}"
                   class="btn btn-secondary">

                    Cancel

                </a>

            </div>

        </form>

    </div>

</div>

@endsection