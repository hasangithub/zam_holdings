<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Packing;
use App\Models\PackingItem;
use App\Models\PackingItemCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackingController extends Controller
{
    public function index()
    {
        $shipmentPlans  = Packing::latest()->paginate(20);

        return view('packings.index', compact('shipmentPlans'));
    }

    public function create()
    {
        $items = Item::orderBy('name')->get();

        $customers = Customer::orderBy('name')->get();

        return view('packings.create', compact('items', 'customers'));
    }

    public function store(Request $request)
    {


        if ($request->step == 1) {

            $selectedItems = array_map('intval', $request->selected_items);

            $stocks = DB::table('purchase_items')
                ->select('item_id', DB::raw('SUM(remaining_qty) as stock_qty'))
                ->groupBy('item_id')
                ->pluck('stock_qty', 'item_id');

            $categories = Category::with(['items' => function ($q) use ($selectedItems) {

                $q->whereIn('id', $selectedItems)
                    ->orderBy('name');
            }])->orderBy('name')->get();

            $categories->each(function ($category) use ($stocks) {

                $category->items->each(function ($item) use ($stocks) {

                    $item->stock_qty = $stocks[$item->id] ?? 0;
                });
            });

            // Remove categories without selected items
            $categories = $categories->filter(function ($category) {
                return $category->items->count() > 0;
            });

            $customers = Customer::orderBy('name')->get();

            return view('packings.create-step2', compact(
                'categories',
                'customers'
            ));
        }


        DB::transaction(function () use ($request) {

            $packing = Packing::create([
                'packing_no' => $this->generatePlanNo(),
                'packing_date' => $request->shipment_date,
                'remarks' => $request->remarks,
                'status' => 'Draft',
            ]);

            foreach ($request->items as $item) {

                $customerTotal = 0;

                if (!empty($item['customers'])) {
                    foreach ($item['customers'] as $customer) {
                        $customerTotal += (float) ($customer['packed_weight'] ?? 0);
                    }
                }

                $reject = (float) ($item['reject'] ?? 0);
                $market = (float) ($item['market'] ?? 0);
                $stock  = (float) ($item['stock'] ?? 0);

                $allocated = $customerTotal + $reject + $market + $stock;

                $availableWeight = DB::table('purchase_items')
                    ->where('item_id', $item['item_id'])
                    ->sum('remaining_qty');

                if ($allocated > $availableWeight) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => ["Allocated quantity exceeds available stock for item ID {$item['item_id']}."]
                    ]);
                }

                $packingItem = PackingItem::create([
                    'packing_id' => $packing->id,
                    'item_id'    => $item['item_id'],
                    'stock'      => $stock,
                    'reject'     => $reject,
                    'market'     => $market,
                    'actual_weight' => $item['actual_weight'] ?? 0,
                ]);

                if (!empty($item['customers'])) {
                    foreach ($item['customers'] as $customerId => $customer) {

                        $weight = (float) ($customer['packed_weight'] ?? 0);

                        if ($weight <= 0) {
                            continue;
                        }

                        PackingItemCustomer::create([
                            'packing_item_id' => $packingItem->id,
                            'customer_id'     => $customerId,
                            'packed_weight'   => $weight,
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route('packings.index')
            ->with('success', 'Shipment Plan Created');
    }

    public function show(int $id)
    {
        $customers = Customer::all();
        $packing = Packing::with([
            'items.item.category',
            'items.customers.customer',
        ])->findOrFail($id);


        return view('packings.show', compact(
            'packing',
            'customers'
        ));
    }


    private function generatePlanNo()
    {
        $last = Packing::latest('id')->first();

        $number = $last ? $last->id + 1 : 1;

        return 'SW-' . str_pad(
            $number,
            5,
            '0',
            STR_PAD_LEFT
        );
    }
}
