<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function profile()
    {
        return view('settings.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'contact_number' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]*$/'],
        ], [
            'contact_number.regex' => 'The contact number may only contain digits, spaces, + ( ) and -.',
        ]);

        $request->user()->update([
            'contact_number' => $validated['contact_number'] ?? null,
        ]);

        return redirect()->route('settings.profile')->with('status', 'Profile updated.');
    }

    public function password()
    {
        return view('settings.password', ['user' => auth()->user()]);
    }
}
