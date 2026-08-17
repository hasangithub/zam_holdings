@extends('layouts.app')

@section('title','Create Shipment Weight')

@section('content')

<div class="container-fluid">

    <div class="card card-primary card-outline">

        <div class="card-header">
            <h3 class="card-title">Step 1: Select Items</h3>
        </div>

        <div class="card-body">

            <form method="POST" action="{{ route('packings.store') }}">
                @csrf
               
                <input type="hidden" name="step" value="1">
                <div class="form-group">

                    <label>Select Items</label>

                    <select name="selected_items[]" class="tom-select" multiple>

                        @foreach($items as $item)
                        <option value="{{ $item->id }}" data-search="{{ $item->name }} {{ $item->item_code }} {{ $item->category->name ?? '' }}">
                            {{ $item->name }}
                        </option>
                        @endforeach

                    </select>

                </div>

                <button type="submit"
                    class="btn btn-primary float-right">

                    Next Step →

                </button>

            </form>

        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        new TomSelect('.tom-select', {
            plugins: ['remove_button'],

            searchField: [
                'text',
                'search'
            ],

            placeholder: 'Select items...'
        });

    });
</script>
@endpush