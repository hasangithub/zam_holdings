<?php

use Livewire\Component;

new class extends Component
{
    
};
?>

<div class="card card-primary">

    <div class="card-header">
        <h3>Export POS Terminal (USD)</h3>
    </div>

    <div class="card-body">

        {{-- CUSTOMER --}}
        <select wire:model="customer_id" class="form-control mb-3">
            <option value="">Select Customer</option>
            @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>

        {{-- EXCHANGE RATE --}}
        <input type="number"
               wire:model="exchange_rate"
               class="form-control mb-3"
               placeholder="Exchange Rate">

        {{-- ITEMS --}}
        <table class="table table-bordered">

            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>USD Price</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

                @foreach($items as $i => $item)

                <tr>

                    <td>
                        <select wire:model="items.{{ $i }}.group_key"
                                class="form-control">

                            <option value="">Select Item</option>

                            @foreach($this->stocks as $s)
                                <option value="{{ $s->item_id }}|{{ $s->price }}|{{ $s->total_qty }}">
                                    {{ $s->item_name }}
                                    | Cost: {{ $s->price }}
                                    | Stock: {{ $s->total_qty }}
                                </option>
                            @endforeach

                        </select>
                    </td>

                    <td>
                        <input type="number"
                               wire:model="items.{{ $i }}.qty"
                               class="form-control">
                    </td>

                    <td>
                        <input type="number"
                               wire:model="items.{{ $i }}.sale_price_foreign"
                               class="form-control">
                    </td>

                    <td>
                        <button wire:click="removeItem({{ $i }})"
                                class="btn btn-danger btn-sm">
                            X
                        </button>
                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

        <button wire:click="addItem" class="btn btn-primary">
            + Add Item
        </button>

        <button wire:click="save" class="btn btn-success float-right">
            Save Export Sale
        </button>

    </div>

</div>