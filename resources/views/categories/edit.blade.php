@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')

<div class="row justify-content-center">

    <div class="col-lg-12">

        <div class="card card-default">

            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-1"></i>
                    Edit Category
                </h3>
            </div>

            <form method="POST" action="{{ route('categories.update', $category->id) }}">
                @csrf
                @method('PUT')

                <div class="card-body">

                    <div class="form-group">

                        <label>
                            Category Name
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="name"
                               value="{{ old('name', $category->name) }}"
                               class="form-control"
                               required>

                    </div>

                    <div class="form-group">

                        <label>
                            Description
                        </label>

                        <textarea name="description"
                                  rows="3"
                                  class="form-control">{{ old('description', $category->description) }}</textarea>

                    </div>

                </div>

                <div class="card-footer">

                    <a href="{{ route('categories.index') }}"
                       class="btn btn-secondary">

                        Back

                    </a>

                    <button type="submit"
                            class="btn btn-warning float-right">

                        Update Category

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection