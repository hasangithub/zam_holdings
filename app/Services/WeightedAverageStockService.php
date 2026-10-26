<?php 

namespace App\Services;

use App\Models\BranchItemStock;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WeightedAverageStockService
{
    /**
     * Add purchased stock and recalculate weighted average cost.
     *
     * Must be called inside the controller's DB transaction.
     */
    public function addPurchase(
        int $branchId,
        int $itemId,
        string|float $qty,
        string|float $purchaseCost
    ): BranchItemStock {
        $qty = (float) $qty;
        $purchaseCost = (float) $purchaseCost;

        if (!is_finite($qty) || !is_finite($purchaseCost)
            || $qty <= 0 || $purchaseCost < 0) {
            throw new InvalidArgumentException(
                'Purchase quantity must be positive and cost cannot be negative.'
            );
        }

        // Create the stock row if this is the first purchase
        // for this item in this branch.
        DB::table('branch_item_stocks')->insertOrIgnore([
            'branch_id' => $branchId,
            'item_id' => $itemId,
            'average_cost' => 0,
            'qty' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Lock the stock row before calculating the new average.
        $stock = BranchItemStock::where('branch_id', $branchId)
            ->where('item_id', $itemId)
            ->lockForUpdate()
            ->firstOrFail();

        $oldQty = (float) $stock->qty;
        $oldAverageCost = (float) $stock->average_cost;

        $newQty = $oldQty + $qty;

        $newAverageCost = (
            ($oldQty * $oldAverageCost) +
            ($qty * $purchaseCost)
        ) / $newQty;

        $stock->qty = round($newQty, 3);
        $stock->average_cost = round($newAverageCost, 2);
        $stock->save();

        return $stock;
    }
}