<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AccountType;
use App\Models\AccountGroup;
use App\Models\Ledger;
use App\Models\SubLedger;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        $accountTypes = AccountType::with(['accountGroups',  'accountGroups.ledgers' , 'accountGroups.ledgers.subLedgers'
        ])
        ->get();

        $statistics = [
            'account_types' => AccountType::count(),
            'account_groups' => AccountGroup::count(),
            'ledgers'       => Ledger::count(),
            'sub_ledgers'   => SubLedger::count(),
        ];

        return view(
            'accounting.chart-of-accounts.index',
            compact('accountTypes', 'statistics')
        );
    }
}