<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Show the form that asks for the email address to send a reset link to.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Email a password reset link, if the address belongs to an account.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // The reply is the same whether the address is unknown, a link was just sent, or one
        // is sent now, so this form cannot be used to find out who has an account.
        Password::sendResetLink($request->only('email'));

        return back()
            ->withInput($request->only('email'))
            ->with('status', 'If an account exists for that email address, we have sent it a link to reset the password.');
    }
}
