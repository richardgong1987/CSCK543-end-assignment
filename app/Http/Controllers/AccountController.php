<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /**
     * The settings page: the user's details, their password, and deleting the account.
     */
    public function edit(Request $request): View
    {
        return view('account.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $details = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // The user's own address is not "taken", so keeping it must pass.
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->update($details);

        return back()->with('status', 'Your details have been saved.');
    }

    /**
     * Delete the account once the user confirms their password. Their favourites and
     * ratings go with it, through the cascading foreign keys on those tables.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // A named bag, because the password form on the same page also has a "password" field.
        $request->validateWithBag('deleteAccount', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::guard('web')->logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Your account has been deleted.');
    }
}
