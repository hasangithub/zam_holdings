<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private $logoTypes = [
        'local-sales' => 'Local Sales Logo',
        'export-sales' => 'Export Sales Logo',
        'purchase' => 'Purchase Logo',
        'common' => 'Common Logo',
    ];

    public function invoiceLogo()
    {
        $directory = public_path('images/invoice');

        $logos = [];

        foreach ($this->logoTypes as $type => $title) {
            $folder = $directory . DIRECTORY_SEPARATOR . $type;

            $logo = collect(['png', 'jpg', 'jpeg'])
                ->map(fn ($extension) => "logo.{$extension}")
                ->first(fn ($filename) => file_exists(
                    $folder . DIRECTORY_SEPARATOR . $filename
                ));

            $logos[$type] = [
                'title' => $title,
                'filename' => $logo,
                'url' => $logo
                    ? asset("images/invoice/{$type}/{$logo}")
                    : null,
                'path' => $logo
                    ? $folder . DIRECTORY_SEPARATOR . $logo
                    : null,
            ];
        }

        return view('settings.invoice-logo', compact('logos'));
    }

    public function updateInvoiceLogo(Request $request, $type)
    {
        abort_unless(array_key_exists($type, $this->logoTypes), 404);

        $request->validate([
            'logo' => [
                'required',
                'image',
                'mimes:png,jpg,jpeg',
                'max:2048',
            ],
        ]);

        $directory = public_path("images/invoice/{$type}");

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Remove only the previous logo for this type.
        foreach (['png', 'jpg', 'jpeg'] as $extension) {
            $oldFile = $directory . DIRECTORY_SEPARATOR . "logo.{$extension}";

            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $extension = strtolower($request->file('logo')->extension());

        $request->file('logo')->move($directory, "logo.{$extension}");

        return redirect()
            ->route('settings.invoice-logo')
            ->with('success', $this->logoTypes[$type] . ' updated successfully.');
    }
}