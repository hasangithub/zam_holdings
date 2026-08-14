<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AccountType;
use App\Models\AccountGroup;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use App\Models\Ledger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JournalEntryController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $journalEntries = JournalEntry::with('branch')
            ->withCount('details')
            ->orderByDesc('journal_date')
            ->orderByDesc('id')
            ->paginate(25);

        return view(
            'accounting.journal-entries.index',
            compact('journalEntries')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $accountTypes = AccountType::get();

        /*
        |--------------------------------------------------------------------------
        | Branch
        |--------------------------------------------------------------------------
        |
        | If your branch restriction is already handled by your application,
        | replace this with your existing branch filtering logic.
        |
        */

        $branches = Branch::orderBy('name')
            ->get();

        return view(
            'accounting.journal-entries.create',
            compact(
                'accountTypes',
                'branches'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
        
            'journal_date' => [
                'required',
                'date',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'details' => [
                'required',
                'array',
                'min:2',
            ],

            'details.*.ledger_id' => [
                'required',
                'exists:ledgers,id',
            ],

            'details.*.sub_ledger_id' => [
                'nullable',
                'exists:sub_ledgers,id',
            ],

            'details.*.debit' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'details.*.credit' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);


        $details = $validated['details'];

        $totalDebit = 0;
        $totalCredit = 0;


        foreach ($details as $index => $detail) {

            $debit = (float) ($detail['debit'] ?? 0);
            $credit = (float) ($detail['credit'] ?? 0);

            /*
            |--------------------------------------------------------------------------
            | Each row must contain either Debit or Credit
            |--------------------------------------------------------------------------
            */

            if ($debit <= 0 && $credit <= 0) {

                throw ValidationException::withMessages([
                    "details.$index.debit" =>
                        'Each journal row must have a debit or credit amount.'
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Cannot have both Debit and Credit
            |--------------------------------------------------------------------------
            */

            if ($debit > 0 && $credit > 0) {

                throw ValidationException::withMessages([
                    "details.$index.debit" =>
                        'A journal row cannot contain both debit and credit.'
                ]);
            }


            $totalDebit += $debit;
            $totalCredit += $credit;
        }


        /*
        |--------------------------------------------------------------------------
        | Debit and Credit must balance
        |--------------------------------------------------------------------------
        */

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {

            throw ValidationException::withMessages([
                'details' =>
                    'Journal entry is not balanced. Total debit must equal total credit.'
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Debit/Credit cannot both be zero
        |--------------------------------------------------------------------------
        */

        if ($totalDebit <= 0) {

            throw ValidationException::withMessages([
                'details' =>
                    'Journal entry amount must be greater than zero.'
            ]);
        }


        DB::transaction(function () use (
            $validated,
            $details
        ) {

            $journalEntry = JournalEntry::create([
                'branch_id' => auth()->user()->branch_id,
                'journal_date' => $validated['journal_date'],
                'description' => $validated['description'] ?? null,
                'created_by' => auth()->id(),
            ]);


            foreach ($details as $detail) {

                $debit = (float) ($detail['debit'] ?? 0);
                $credit = (float) ($detail['credit'] ?? 0);

                /*
                |--------------------------------------------------------------------------
                | Validate that selected sub ledger belongs to selected ledger
                |--------------------------------------------------------------------------
                */

                if (!empty($detail['sub_ledger_id'])) {

                    $validSubLedger = \App\Models\SubLedger::where(
                        'id',
                        $detail['sub_ledger_id']
                    )
                    ->where(
                        'ledger_id',
                        $detail['ledger_id']
                    )
                    ->exists();

                    if (!$validSubLedger) {

                        throw ValidationException::withMessages([
                            'details' =>
                                'Invalid sub ledger selected for ledger.'
                        ]);
                    }
                }


                JournalEntryDetail::create([

                    'journal_entry_id' =>
                        $journalEntry->id,

                    'ledger_id' =>
                        $detail['ledger_id'],

                    'sub_ledger_id' =>
                        $detail['sub_ledger_id'] ?? null,

                    'debit' =>
                        $debit,

                    'credit' =>
                        $credit,
                ]);
            }
        });


        return redirect()
            ->route('accounting.journal-entries.index')
            ->with(
                'success',
                'Journal entry created successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(JournalEntry $journalEntry)
    {
        $journalEntry->load([
            'details.ledger.accountGroup.accountType',
            'details.subLedger',
        ]);

        $accountTypes = AccountType::get();

        $branches = Branch::orderBy('name')
            ->get();

        return view(
            'accounting.journal-entries.edit',
            compact(
                'journalEntry',
                'accountTypes',
                'branches'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        JournalEntry $journalEntry
    ) {
        $validated = $request->validate([
        
            'journal_date' => [
                'required',
                'date',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'details' => [
                'required',
                'array',
                'min:2',
            ],

            'details.*.ledger_id' => [
                'required',
                'exists:ledgers,id',
            ],

            'details.*.sub_ledger_id' => [
                'nullable',
                'exists:sub_ledgers,id',
            ],

            'details.*.debit' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'details.*.credit' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);


        $details = $validated['details'];

        $totalDebit = 0;
        $totalCredit = 0;


        foreach ($details as $index => $detail) {

            $debit = (float) ($detail['debit'] ?? 0);
            $credit = (float) ($detail['credit'] ?? 0);


            if ($debit <= 0 && $credit <= 0) {

                throw ValidationException::withMessages([
                    "details.$index.debit" =>
                        'Each journal row must have a debit or credit amount.'
                ]);
            }


            if ($debit > 0 && $credit > 0) {

                throw ValidationException::withMessages([
                    "details.$index.debit" =>
                        'A journal row cannot contain both debit and credit.'
                ]);
            }


            $totalDebit += $debit;
            $totalCredit += $credit;
        }


        if (round($totalDebit, 2) !== round($totalCredit, 2)) {

            throw ValidationException::withMessages([
                'details' =>
                    'Journal entry is not balanced.'
            ]);
        }


        DB::transaction(function () use (
            $journalEntry,
            $validated,
            $details
        ) {

            $journalEntry->update([
                'branch_id' =>  auth()->user()->branch_id,
                'journal_date' => $validated['journal_date'],
                'description' => $validated['description'] ?? null,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Replace existing details
            |--------------------------------------------------------------------------
            */

            $journalEntry->details()->delete();


            foreach ($details as $detail) {

                $debit = (float) ($detail['debit'] ?? 0);
                $credit = (float) ($detail['credit'] ?? 0);


                if (!empty($detail['sub_ledger_id'])) {

                    $validSubLedger =
                        \App\Models\SubLedger::where(
                            'id',
                            $detail['sub_ledger_id']
                        )
                        ->where(
                            'ledger_id',
                            $detail['ledger_id']
                        )
                        ->exists();

                    if (!$validSubLedger) {

                        throw ValidationException::withMessages([
                            'details' =>
                                'Invalid sub ledger selected.'
                        ]);
                    }
                }


                JournalEntryDetail::create([
                    'journal_entry_id' => $journalEntry->id,
                    'ledger_id' => $detail['ledger_id'],
                    'sub_ledger_id' =>
                        $detail['sub_ledger_id'] ?? null,
                    'debit' => $debit,
                    'credit' => $credit,
                ]);
            }
        });


        return redirect()
            ->route('accounting.journal-entries.index')
            ->with(
                'success',
                'Journal entry updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(JournalEntry $journalEntry)
    {
        DB::transaction(function () use ($journalEntry) {

            $journalEntry->deleted_by = auth()->id();
            $journalEntry->save();

            $journalEntry->details()->delete();

            $journalEntry->delete();
        });


        return back()->with(
            'success',
            'Journal entry deleted successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AJAX - Account Groups
    |--------------------------------------------------------------------------
    */

    public function accountGroups(AccountType $accountType)
    {
        $groups = AccountGroup::where(
            'account_type_id',
            $accountType->id
        )
        ->get([
            'id',
            'name',
        ]);

        return response()->json($groups);
    }


    /*
    |--------------------------------------------------------------------------
    | AJAX - Ledgers
    |--------------------------------------------------------------------------
    */

    public function ledgers(AccountGroup $accountGroup)
    {
        $ledgers = Ledger::where(
            'account_group_id',
            $accountGroup->id
        )
        ->get([
            'id',
            'name',
        ]);

        return response()->json($ledgers);
    }


    /*
    |--------------------------------------------------------------------------
    | AJAX - Sub Ledgers
    |--------------------------------------------------------------------------
    */

    public function subLedgers(Ledger $ledger)
    {
        $subLedgers = $ledger->subLedgers()
            ->get([
                'id',
                'name',
            ]);

        return response()->json($subLedgers);
    }
}