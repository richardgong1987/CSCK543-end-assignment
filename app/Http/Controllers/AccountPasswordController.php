<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class AccountPasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        // A named bag, because the delete form on the same page also has a "password" field.
        $passwords = $request->validateWithBag('updatePassword', [
            // Asking for the current password stops anyone at an unattended session taking the account over.
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        // The "hashed" cast on User hashes it on save.
        $request->user()->update(['password' => $passwords['password']]);

        return back()->with('status', 'Your password has been changed.');
    }
}
