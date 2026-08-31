<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class MfaLoginController extends Controller
{
    /**
     * Show the "enter your code" screen. Only reachable if a password
     * check already succeeded this login attempt — see
     * AuthenticatedSessionController::store().
     */
    public function challenge(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('mfa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.mfa-challenge');
    }

    /**
     * Verify the submitted code and, if correct, actually complete the
     * login that was deliberately paused after the password check.
     */
    public function verify(Request $request): RedirectResponse
    {
        if (! $request->session()->has('mfa_user_id')) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = User::find($request->session()->get('mfa_user_id'));

        if (! $user) {
            $request->session()->forget(['mfa_user_id', 'mfa_remember']);
            return redirect()->route('login');
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->mfa_secret, $validated['code']);

        if (! $valid) {
            return back()->withErrors(['code' => 'That code is incorrect or expired. Please try again.']);
        }

        $remember = $request->session()->get('mfa_remember', false);

        $request->session()->forget(['mfa_user_id', 'mfa_remember']);

        Auth::loginUsingId($user->id, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
