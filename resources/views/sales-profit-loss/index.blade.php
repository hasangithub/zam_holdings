@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h4>Sales Profit & Loss</h4>

        <a href="{{ route('sales-profit-loss.create') }}"
           class="btn btn-primary">

            Add Profit & Loss

        </a>

    </div>


    @if(session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif


    <div class="card">

        <div class="card-header">

            <strong>Sales Invoices</strong>

        </div>


        <div class="card-body">


            <form method="GET"
                  action="{{ route('sales-profit-loss.index') }}"
                  class="row mb-3">

                <div class="col-md-4">

                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="form-control"
                           placeholder="Invoice No / Customer">

                </div>


                <div class="col-md-2">

                    <button type="submit"
                            class="btn btn-secondary">

                        Search

                    </button>

                </div>

            </form>


            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Date</th>

                            <th>Invoice No</th>

                            <th>Customer</th>

                            <th class="text-end">
                                Sales Amount
                            </th>

                            <th class="text-end">
                                Cost
                            </th>

                            <th class="text-end">
                                Profit / Loss
                            </th>

                            <th>Status</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                    @forelse($sales as $sale)

                        <tr>

                            <td>
                                {{
                                    $loop->iteration +
                                    (
                                        ($sales->currentPage() - 1)
                                        * $sales->perPage()
                                    )
                                }}
                            </td>


                            <td>

                                {{ $sale->sale_date
                                    ? \Carbon\Carbon::parse(
                                        $sale->sale_date
                                      )->format('d/m/Y')
                                    : '-' }}

                            </td>


                            <td>

                                {{ $sale->invoice_no }}

                            </td>


                            <td>

                                {{ optional(
                                    $sale->customer
                                )->name }}

                            </td>


                            <td class="text-end">

                                @if($sale->profitLoss)

                                    {{
                                        number_format(
                                            $sale->profitLoss
                                                ->total_sales_amount,
                                            2
                                        )
                                    }}

                                @else

                                    -

                                @endif

                            </td>


                            <td class="text-end">

                                @if($sale->profitLoss)

                                    {{
                                        number_format(
                                            $sale->profitLoss
                                                ->total_cost,
                                            2
                                        )
                                    }}

                                @else

                                    -

                                @endif

                            </td>


                            <td class="text-end">

                                @if($sale->profitLoss)

                                    {{
                                        number_format(
                                            $sale->profitLoss
                                                ->total_profit_loss,
                                            2
                                        )
                                    }}

                                @else

                                    -

                                @endif

                            </td>


                            <td>

                                @if($sale->profitLoss)

                                    <span class="badge bg-success">
                                        Completed
                                    </span>

                                @else

                                    <span class="badge bg-warning text-dark">
                                        Pending
                                    </span>

                                @endif

                            </td>


                            <td>

                                <a href="{{
                                    route(
                                        'sales-profit-loss.edit',
                                        $sale
                                    )
                                }}"
                                class="btn btn-sm btn-primary">

                                    @if($sale->profitLoss)
                                        Edit
                                    @else
                                        Create
                                    @endif

                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="9"
                                class="text-center">

                                No sales invoices found.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>


            {{ $sales->links() }}

        </div>

    </div>

</div>

@endsection