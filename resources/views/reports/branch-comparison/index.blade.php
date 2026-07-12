@extends('layouts.app')

@section('title','Branch Sales vs Purchase Comparison')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                Branch Sales vs Purchase Quantity Comparison
            </h5>
        </div>

        <div class="card-body">

            <form action="{{ route('reports.branch-comparison.generate') }}" method="POST">

                @csrf

                <div class="row">

                    <div class="col-md-4">

                        <div class="form-group">

                            <label>Sales Branch</label>

                            <select name="branch_id" class="form-control select2" required>

                                <option value="">Select Branch</option>

                                @foreach($branches as $branch)

                                <option value="{{ $branch->id }}">

                                    {{ $branch->name }}

                                </option>

                                @endforeach

                            </select>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="form-group">

                            <label>Sales Date From</label>

                            <input type="date"
                                name="sales_from"
                                class="form-control"
                                required>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="form-group">

                            <label>Sales Date To</label>

                            <input type="date"
                                name="sales_to"
                                class="form-control"
                                required>

                        </div>

                    </div>

                </div>


                <hr>


                <div class="row">

                    <div class="col-md-3">

                        <div class="form-group">

                            <label>Purchase Supplier</label>

                            <select name="supplier_id" class="form-control select2" required>

                                <option value="">Select Supplier</option>

                                @foreach($suppliers as $supplier)

                                <option value="{{ $supplier->id }}">

                                    {{ $supplier->name }}

                                </option>

                                @endforeach

                            </select>

                        </div>

                    </div>


                    <div class="col-md-3">

                        <div class="form-group">

                            <label>Purchase Date From</label>

                            <input type="date"
                                name="purchase_from"
                                class="form-control"
                                required>

                        </div>

                    </div>


                    <div class="col-md-3">

                        <div class="form-group">

                            <label>Purchase Date To</label>

                            <input type="date"
                                name="purchase_to"
                                class="form-control"
                                required>

                        </div>

                    </div>


                    <div class="col-md-3">

                        <div class="form-group">

                            <label>Item Category</label>

                            <select name="category_id" class="form-control select2">

                                <option value="">All Categories</option>

                                @foreach($categories as $category)

                                <option value="{{ $category->id }}">

                                    {{ $category->name }}

                                </option>

                                @endforeach

                            </select>

                        </div>

                    </div>

                </div>

                <div class="text-end">

                    <button
                        class="btn btn-primary">

                        <i class="fas fa-search"></i>

                        Generate Report

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection