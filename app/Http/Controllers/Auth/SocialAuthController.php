<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login');
        }

        $user = User::where('provider_id', $socialUser->getId())
            ->where('provider', $provider)
            ->first();

        if (!$user) {
            $existingUser = User::where('email', $socialUser->getEmail())->first();

            if ($existingUser) {
                $existingUser->update([
                    'provider_id' => $socialUser->getId(),
                    'provider' => $provider,
                ]);
                $user = $existingUser;
            } else {
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'email_verified_at' => now(),
                    'password' => bcrypt(Str::random(24)),
                ]);
            }
        }

        Auth::login($user);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
