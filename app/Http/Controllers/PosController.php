<?php 

namespace App\Http\Controllers;

use App\Models\Ledger;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function local()
    {
        return view('pos.local');
    }

    public function export()
    {
        return view('pos.export');
    }
}