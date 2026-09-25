<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $google = Socialite::driver('google')->user();
        $user = User::where('google_id', $google->getId())->first()
            ?? User::where('email', $google->getEmail())->first();
        if (! $user) {
            $user = User::create([
                'name' => $google->getName() ?: explode('@', (string) $google->getEmail())[0],
                'email' => $google->getEmail(),
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
            ]);
            $user->forceFill(['google_id' => $google->getId()])->save();
        } else {
            abort_unless($user->is_active, 403, 'บัญชีนี้ถูกระงับการใช้งาน');
            $user->forceFill([
                'google_id' => $user->google_id ?? $google->getId(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        }
        Auth::login($user, true);

        return redirect()->intended(route('dashboard'));
    }
}
