<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\Purchase;

class DashboardController extends Controller
{
    public function index()
    {
        // Monthly Sales
        $sales = Sale::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('month')
            ->pluck('total','month');

        // Monthly Purchases
        $purchases = Purchase::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('month')
            ->pluck('total','month');

        // Format for 12 months
        $months = [];
        $salesData = [];
        $purchaseData = [];
        $profitData = [];

        for ($i = 1; $i <= 12; $i++) {

            $months[] = date("M", mktime(0,0,0,$i,1));

            $s = $sales[$i] ?? 0;
            $p = $purchases[$i] ?? 0;

            $salesData[] = $s;
            $purchaseData[] = $p;
            $profitData[] = $s - $p;
        }

        return view('dashboard', compact(
            'months',
            'salesData',
            'purchaseData',
            'profitData'
        ));
    }
}