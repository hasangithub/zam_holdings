<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SettingsController extends Controller
{

    public function invoiceLogo()
    {
        $directory = public_path('images/invoice');

        $logo = collect(['png', 'jpg', 'jpeg'])
            ->map(fn($extension) => "logo.{$extension}")
            ->first(fn($filename) => file_exists($directory . DIRECTORY_SEPARATOR . $filename));

        return view('settings.invoice-logo', [
            'logo' => $logo,
        ]);
    }

    public function updateInvoiceLogo(Request $request)
    {
        $request->validate([
            'logo' => [
                'required',
                'image',
                'mimes:png,jpg,jpeg',
                'max:2048',
            ],
        ]);

        $directory = public_path('images/invoice');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Remove the previous logo, regardless of its extension.
        foreach (['png', 'jpg', 'jpeg'] as $extension) {
            $oldFile = $directory . DIRECTORY_SEPARATOR . "logo.{$extension}";

            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        // Save the uploaded image with its original extension.
        $extension = strtolower($request->file('logo')->extension());

        $request->file('logo')->move($directory, "logo.{$extension}");

        return redirect()
            ->route('settings.invoice-logo')
            ->with('success', 'Invoice logo updated successfully.');
    }
}
