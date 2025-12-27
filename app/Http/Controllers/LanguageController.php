<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    /**
     * Update user's language preference.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'language' => 'required|in:id,en',
        ]);

        $request->user()->update([
            'language' => $request->language,
        ]);

        return back();
    }
}
