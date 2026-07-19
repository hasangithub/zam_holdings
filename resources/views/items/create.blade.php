@extends('layouts.app')

@section('title', 'Create Item')

@section('content')

<div class="row justify-content-center">

    <div class="col-lg-12">

        <div class="card card-default">

            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-box mr-1"></i>
                    Create New Item
                </h3>
            </div>

            <form method="POST" action="{{ route('items.store') }}">
                @csrf

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            {{-- CATEGORY --}}
                            <div class="form-group">
                                <label>
                                    Category
                                    <span class="text-danger">*</span>
                                </label>

                                <select name="category_id"
                                    class="form-control @error('category_id') is-invalid @enderror"
                                    required>

                                    <option value="">
                                        Select Category
                                    </option>

                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                    @endforeach

                                </select>

                                @error('category_id')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item_type">Item Type <span class="text-danger">*</span></label>

                                <select name="item_type" id="item_type" class="form-control" required>
                                    <option value="">-- Select Item Type --</option>

                                    @foreach($itemTypes as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('item_type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        {{-- ITEM NAME --}}
                        <div class="col-md-8">

                            <div class="form-group">

                                <label>
                                    Item Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Enter item name"
                                    required>

                                @error('name')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                                @enderror

                            </div>

                        </div>

                        {{-- ITEM CODE --}}
                        <div class="col-md-4">

                            <div class="form-group">

                                <label>
                                    Item Code
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                    name="item_code"
                                    value="{{ old('item_code') }}"
                                    class="form-control @error('item_code') is-invalid @enderror"
                                    placeholder="ITM001"
                                    required>

                                @error('item_code')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                                @enderror

                            </div>

                        </div>

                    </div>

                </div>

                <div class="card-footer">

                    <a href="{{ route('items.index') }}"
                        class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Back
                    </a>

                    <button type="submit"
                        class="btn btn-success float-right">
                        <i class="fas fa-save mr-1"></i>
                        Save Item
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection