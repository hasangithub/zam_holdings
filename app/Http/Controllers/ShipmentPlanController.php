<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Customer;
use App\Models\ShipmentPlan;
use App\Models\ShipmentPlanItem;
use App\Models\ShipmentPlanItemCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentPlanController extends Controller
{
    public function index()
    {
        $shipmentPlans  = ShipmentPlan::latest()->paginate(20);

        return view('shipment_plans.index', compact('shipmentPlans'));
    }

    public function create()
    {
        $items = Item::orderBy('name')->get();

        $customers = Customer::orderBy('name')->get();

        return view('shipment_plans.create', compact('items', 'customers'));
    }

    public function store(Request $request)
    {


        if ($request->step == 1) {

            $selectedItems = array_map('intval', $request->selected_items);

            $stocks = DB::table('purchase_items')
                ->select('item_id', DB::raw('SUM(remaining_qty) as stock_qty'))
                ->groupBy('item_id')
                ->pluck('stock_qty', 'item_id');

            $items = Item::whereIn('id', $selectedItems)
                ->get()
                ->map(function ($item) use ($stocks) {
                    $item->stock_qty = $stocks[$item->id] ?? 0;
                    return $item;
                });

            $customers = Customer::all();

            return view('shipment_plans.create-step2', compact('items', 'customers'));
        }


        DB::transaction(function () use ($request) {

            $plan = ShipmentPlan::create([
                'plan_no' => $this->generatePlanNo(),
                'shipment_date' => $request->shipment_date,
                'remarks' => $request->remarks,
                'status' => 'Draft',
            ]);

            if ($request->allocations) {

                foreach ($request->allocations as $item) {

                    $shipmentPlanItem = ShipmentPlanItem::create([

                        'shipment_plan_id'     => $plan->id,
                        'item_id'        => $item['item_id'],
                    ]);

                    if (!empty($item['customers'])) {

                        foreach ($item['customers'] as $customerId => $weight) {
                                                
                            if (($weight ?? 0) <= 0) {
                                continue;
                            }

                            ShipmentPlanItemCustomer::create([
                                'shipment_plan_item_id' => $shipmentPlanItem->id,
                                'customer_id'     => $customerId,
                                'planned_weight'   => $weight
                            ]);
                        }
                    }
                }
            }
        });

        return redirect()
            ->route('shipment-plans.index')
            ->with('success', 'Shipment Plan Created');
    }

    public function edit(int $id)
    {
        $plan = ShipmentPlan::with('items')->findOrFail($id);

        $items = Item::orderBy('name')->get();

        $customers = Customer::orderBy('name')->get();

        return view(
            'shipment-plans.edit',
            compact(
                'plan',
                'items',
                'customers'
            )
        );
    }

    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {

            $plan = ShipmentPlan::findOrFail($id);

            $plan->update([
                'shipment_date' => $request->shipment_date,
                'remarks' => $request->remarks,
            ]);

            ShipmentPlanItem::where(
                'shipment_plan_id',
                $plan->id
            )->delete();

            if ($request->allocations) {

                foreach ($request->allocations as $itemId => $customers) {

                    foreach ($customers as $customerId => $weight) {

                        if (empty($weight) || $weight <= 0) {
                            continue;
                        }

                        ShipmentPlanItem::create([
                            'shipment_plan_id' => $plan->id,
                            'item_id' => $itemId,
                            'customer_id' => $customerId,
                            'planned_weight' => $weight,
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route('shipment-plans.index')
            ->with('success', 'Shipment Plan Updated');
    }

    public function show(int $id)
    {
    
        $customers = Customer::orderBy('name')->get();

        $shipmentPlan = ShipmentPlan::with([
            'items.item',
            'items.customers.customer'
        ])->findOrFail($id);

        return view('shipment_plans.show', compact(
            'shipmentPlan',
            'customers',
        ));
    }

    public function destroy($id)
    {
        $plan = ShipmentPlan::findOrFail($id);

        $plan->delete();

        return redirect()
            ->route('shipment-plans.index')
            ->with('success', 'Shipment Plan Deleted');
    }

    private function generatePlanNo()
    {
        $last = ShipmentPlan::latest('id')->first();

        $number = $last ? $last->id + 1 : 1;

        return 'SP-' . str_pad(
            $number,
            5,
            '0',
            STR_PAD_LEFT
        );
    }
}
