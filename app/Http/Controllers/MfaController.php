<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

class MfaController extends Controller
{
    /**
     * Show the MFA settings page — either the "set up" screen (if not
     * yet enabled) or the "MFA is active" confirmation screen.
     */
    public function show()
    {
        $user = auth()->user();
        $google2fa = new Google2FA();

        $qrCodeUrl = null;
        $secret = null;

        if (!$user->mfa_enabled) {
            // Generate a new secret if one doesn't already exist for
            // this in-progress enrollment
            if (!$user->mfa_secret) {
                $secret = $google2fa->generateSecretKey();
                $user->update(['mfa_secret' => $secret]);
            } else {
                $secret = $user->mfa_secret;
            }

            $qrCodeUrl = $google2fa->getQRCodeUrl(
                config('app.name', 'ALM System'),
                $user->email,
                $secret
            );
        }

        return view('settings.mfa', compact('user', 'qrCodeUrl', 'secret'));
    }

    /**
     * Confirm enrollment — user enters a code from their authenticator
     * app to prove the setup actually worked before we turn MFA on.
     */
    public function enable(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = auth()->user();
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($user->mfa_secret, $validated['code']);

        if (!$valid) {
            return back()->withErrors(['code' => 'That code is incorrect or expired. Please try again.']);
        }

        $user->update([
            'mfa_enabled'      => true,
            'mfa_confirmed_at' => now(),
        ]);

        return redirect()->route('settings.mfa')
            ->with('status', 'Multi-factor authentication is now enabled on your account.');
    }

    /**
     * Turn MFA off — requires the current password as confirmation,
     * since disabling MFA is a meaningful security downgrade.
     */
    public function disable(Request $request)
    {
        $validated = $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        auth()->user()->update([
            'mfa_enabled'      => false,
            'mfa_secret'       => null,
            'mfa_confirmed_at' => null,
        ]);

        return redirect()->route('settings.mfa')
            ->with('status', 'Multi-factor authentication has been disabled.');
    }
}
