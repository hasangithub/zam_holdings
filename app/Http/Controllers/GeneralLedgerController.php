<?php

namespace App\Http\Controllers;

use App\Models\Ledger;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GeneralLedgerController extends Controller
{
    public function index()
    {
        $ledgers = Ledger::orderBy('name')->get();

        return view('reports.general-ledger', compact('ledgers'));
    }

    public function report(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'ledger_id' => 'required|integer|exists:ledgers,id',
        ]);

        $fromDate = Carbon::parse($request->from_date);
        $toDate   = Carbon::parse($request->to_date);

        $branchId = auth()->user()->branch_id;

        /*
        |--------------------------------------------------------------------------
        | Selected Ledger
        |--------------------------------------------------------------------------
        */

        $ledger = Ledger::findOrFail($request->ledger_id);

        /*
        |--------------------------------------------------------------------------
        | Get all journal details for this ledger
        |--------------------------------------------------------------------------
        */

        $details = \App\Models\JournalEntryDetail::with([
                'journalEntry',
                'subLedger',
            ])
            ->where('ledger_id', $ledger->id)
            ->whereHas('journalEntry', function ($query) use (
                $branchId,
                $fromDate,
                $toDate
            ) {
                $query->where('branch_id', $branchId)
                    ->whereBetween('journal_date', [
                        $fromDate->toDateString(),
                        $toDate->toDateString()
                    ]);
            })
            ->get()
            ->sortBy(function ($detail) {
                return $detail->journalEntry->journal_date
                    . '-' .
                    str_pad($detail->journalEntry->id, 10, '0', STR_PAD_LEFT);
            });

        /*
        |--------------------------------------------------------------------------
        | Check whether this ledger uses subledgers
        |--------------------------------------------------------------------------
        */

        $hasSubLedgers = $details->contains(function ($detail) {
            return !is_null($detail->sub_ledger_id);
        });

        /*
        |--------------------------------------------------------------------------
        | Opening balances
        |--------------------------------------------------------------------------
        */

        $openingDetails = \App\Models\JournalEntryDetail::where(
                'ledger_id',
                $ledger->id
            )
            ->whereHas('journalEntry', function ($query) use (
                $branchId,
                $fromDate
            ) {
                $query->where('branch_id', $branchId)
                    ->where(
                        'journal_date',
                        '<',
                        $fromDate->toDateString()
                    );
            })
            ->get();

        /*
        |--------------------------------------------------------------------------
        | SUBLEDGER-WISE REPORT
        |--------------------------------------------------------------------------
        */

        $groups = collect();

        if ($hasSubLedgers) {

            $subLedgerIds = $details
                ->whereNotNull('sub_ledger_id')
                ->pluck('sub_ledger_id')
                ->unique();

            foreach ($subLedgerIds as $subLedgerId) {

                $subLedger = \App\Models\SubLedger::find($subLedgerId);

                if (!$subLedger) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Opening
                |--------------------------------------------------------------------------
                */

                $opening = $openingDetails
                    ->where('sub_ledger_id', $subLedgerId)
                    ->sum(function ($detail) {
                        return (float) $detail->debit
                            - (float) $detail->credit;
                    });

                $balance = $opening;

                $rows = [];

                $subDetails = $details
                    ->where('sub_ledger_id', $subLedgerId);

                foreach ($subDetails as $detail) {

                    $debit = (float) $detail->debit;
                    $credit = (float) $detail->credit;

                    $balance += $debit - $credit;

                    $rows[] = [
                        'date' => $detail->journalEntry->journal_date,
                        'journal_id' => $detail->journalEntry->id,
                        'description' => $detail->journalEntry->description,
                        'debit' => $debit,
                        'credit' => $credit,
                        'balance' => $balance,
                    ];
                }

                $groups->push([
                    'sub_ledger' => $subLedger,
                    'opening' => $opening,
                    'rows' => $rows,
                    'total_debit' => collect($rows)->sum('debit'),
                    'total_credit' => collect($rows)->sum('credit'),
                    'closing' => $balance,
                ]);
            }

        } else {

            /*
            |--------------------------------------------------------------------------
            | LEDGER-WISE REPORT
            |--------------------------------------------------------------------------
            */

            $opening = $openingDetails->sum(function ($detail) {
                return (float) $detail->debit
                    - (float) $detail->credit;
            });

            $balance = $opening;

            $rows = [];

            foreach ($details as $detail) {

                $debit = (float) $detail->debit;
                $credit = (float) $detail->credit;

                $balance += $debit - $credit;

                $rows[] = [
                    'date' => $detail->journalEntry->journal_date,
                    'journal_id' => $detail->journalEntry->id,
                    'description' => $detail->journalEntry->description,
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $balance,
                ];
            }

            $groups->push([
                'sub_ledger' => null,
                'opening' => $opening,
                'rows' => $rows,
                'total_debit' => collect($rows)->sum('debit'),
                'total_credit' => collect($rows)->sum('credit'),
                'closing' => $balance,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | All ledgers for dropdown
        |--------------------------------------------------------------------------
        */

        $ledgers = Ledger::orderBy('name')->get();

        return view('reports.general-ledger', compact(
            'ledgers',
            'ledger',
            'groups',
            'fromDate',
            'toDate',
            'hasSubLedgers'
        ));
    }
}