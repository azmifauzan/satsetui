<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Profile Controller
 *
 * Handles user profile viewing and updating (name, email, phone).
 */
class ProfileController extends Controller
{
    /**
     * Display the user's profile page.
     */
    public function edit(): \Inertia\Response
    {
        $user = Auth::user();

        return Inertia::render('Profile/Edit', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(UpdateProfileRequest $request): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        // Check if email changed — if so, reset verification
        $emailChanged = $validated['email'] !== $user->email;

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        if ($emailChanged) {
            $user->email_verified_at = null;
            $user->save();
            $user->sendEmailVerificationNotification();
        }

        return back()->with('success', true);
    }
}
