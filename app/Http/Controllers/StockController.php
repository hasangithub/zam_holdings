<?php

namespace App\Http\Controllers;

use App\Models\BranchItemStock;
use App\Models\PurchaseItem;

class StockController extends Controller
{
    public function summary()
    {
        $branchId = (int) auth()->user()->branch_id;

        $stocks = BranchItemStock::with('item')
            ->where('branch_id', $branchId)
            ->select(
                'item_id',
                'qty as stock_qty',
                'average_cost as avg_price'
            )
            ->selectRaw('average_cost * qty as stock_value')
            ->orderBy('item_id')
            ->get()
            ->map(function ($stock) use ($branchId) {
                $stock->purchased_qty = PurchaseItem::where('item_id', $stock->item_id)
                    ->whereHas('purchase', function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)
                            ->where('status', '!=', 'cancelled');
                    })
                    ->sum('qty');

                return $stock;
            });

        return view('inventory.stock-summary', compact('stocks'));
    }
}
