<?php

namespace App\Http\Controllers;

use App\Models\InvoiceCompanyProfile;
use Illuminate\Http\Request;

class InvoiceCompanyProfileController extends Controller
{
    public function index()
    {
        $profiles = InvoiceCompanyProfile::latest('id')->paginate(20);

        return view('invoice-company-profiles.index', compact('profiles'));
    }

    public function create()
    {
        return view('invoice-company-profiles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'sub_title_1' => ['nullable', 'string', 'max:255'],
            'sub_title_2' => ['nullable', 'string', 'max:255'],
            'address'     => ['nullable', 'string', 'max:2000'],
            'status'      => ['required', 'boolean'],
        ]);

        InvoiceCompanyProfile::create($validated);

        return redirect()
            ->route('invoice-company-profiles.index')
            ->with('success', 'Invoice company profile created successfully.');
    }

    public function edit(InvoiceCompanyProfile $invoice_company_profile)
    {
        return view('invoice-company-profiles.edit', [
            'profile' => $invoice_company_profile,
        ]);
    }

    public function update(
        Request $request,
        InvoiceCompanyProfile $invoice_company_profile
    ) {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'sub_title_1' => ['nullable', 'string', 'max:255'],
            'sub_title_2' => ['nullable', 'string', 'max:255'],
            'address'     => ['nullable', 'string', 'max:2000'],
            'status'      => ['required', 'boolean'],
        ]);

        $invoice_company_profile->update($validated);

        return redirect()
            ->route('invoice-company-profiles.index')
            ->with('success', 'Invoice company profile updated successfully.');
    }
}