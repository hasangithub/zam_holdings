@extends('layouts.app')

@section('title','Shipment Plans')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="row mb-3">

        <div class="col-md-6">
            <h4 class="mb-0">
                <i class="fas fa-truck mr-1"></i> Shipment Plans
            </h4>
        </div>

        <div class="col-md-6 text-right">
            <a href="{{ route('shipment-plans.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create Plan
            </a>
        </div>

    </div>

    {{-- TABLE CARD --}}
    <div class="card card-outline card-primary">

        <div class="card-body">

            <div class="table-responsive">

                <table id="shipmentTable" class="table table-bordered table-striped table-sm">

                    <thead class="bg-light">
                        <tr>
                            <th>Plan No</th>
                            <th>Date</th>
                            <th>Total Items</th>
                            <th>Total Qty</th>
                            <th>Status</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($shipmentPlans as $plan)

                            <tr>

                                <td>
                                    <strong>{{ $plan->plan_no ?? 'SP-'.$plan->id }}</strong>
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($plan->plan_date)->format('Y-m-d') }}
                                </td>

                                <td>
                                    <span class="badge badge-info">
                                        {{ $plan->items_count ?? 0 }}
                                    </span>
                                </td>

                                <td>
                                    {{ $plan->total_qty ?? 0 }}
                                </td>

                                <td>
                                    @if($plan->status == 'draft')
                                        <span class="badge badge-warning">Draft</span>
                                    @elseif($plan->status == 'confirmed')
                                        <span class="badge badge-success">Confirmed</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($plan->status) }}</span>
                                    @endif
                                </td>

                                <td>

                                    <a href="{{ route('shipment-plans.show', $plan->id) }}"
                                       class="btn btn-info btn-xs">

                                        <i class="fas fa-eye"></i>

                                    </a>

                                    <form action="{{ route('shipment-plans.destroy', $plan->id) }}"
                                          method="POST"
                                          style="display:inline-block">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-danger btn-xs"
                                                onclick="return confirm('Delete this plan?')">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>
$(document).ready(function () {

    $('#shipmentTable').DataTable({
        responsive: true,
        pageLength: 10,
        autoWidth: false,
        ordering: true,
        searching: true
    });

});
</script>

@endpush