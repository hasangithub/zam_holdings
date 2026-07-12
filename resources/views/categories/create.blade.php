@extends('layouts.app')

@section('title', 'Create Category')

@section('content')

<div class="row justify-content-center">

    <div class="col-lg-12">

        <div class="card card-default">

            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-folder-plus mr-1"></i>
                    Create Category
                </h3>
            </div>

            <form method="POST" action="{{ route('categories.store') }}">
                @csrf

                <div class="card-body">

                    <div class="form-group">

                        <label>
                            Category Name
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Enter Category Name"
                               required>

                        @error('name')
                            <span class="invalid-feedback">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>

                    <div class="form-group">

                        <label>
                            Description
                        </label>

                        <textarea name="description"
                                  rows="3"
                                  class="form-control"
                                  placeholder="Optional description"></textarea>

                    </div>

                </div>

                <div class="card-footer">

                    <a href="{{ route('categories.index') }}"
                       class="btn btn-secondary">

                        <i class="fas fa-arrow-left mr-1"></i>
                        Back

                    </a>

                    <button type="submit"
                            class="btn btn-success float-right">

                        <i class="fas fa-save mr-1"></i>
                        Save Category

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection