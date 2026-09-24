<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Provider yang didukung.
     */
    private const SUPPORTED_PROVIDERS = ['google', 'facebook', 'tiktok'];

    /**
     * Redirect user ke OAuth provider.
     */
    public function redirect(string $provider)
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle callback dari OAuth provider.
     */
    public function callback(string $provider)
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Login sosial gagal. Silakan coba lagi.');
        }

        // Cari user berdasarkan provider_id atau email
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        // Jika tidak ditemukan by provider, coba cari by email
        if (! $user && $socialUser->getEmail()) {
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Update user yang sudah ada dengan info sosial
                $user->update([
                    'provider'    => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar'      => $socialUser->getAvatar(),
                ]);
            }
        }

        // Buat user baru jika belum ada
        if (! $user) {
            $user = User::create([
                'name'              => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'email'             => $socialUser->getEmail() ?? $socialUser->getId().'@'.$provider.'.social',
                'password'          => bcrypt(Str::random(32)),
                'provider'          => $provider,
                'provider_id'       => $socialUser->getId(),
                'avatar'            => $socialUser->getAvatar(),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended('/');
    }

    /**
     * Validasi provider yang dikirim.
     */
    private function validateProvider(string $provider): void
    {
        abort_unless(
            in_array($provider, self::SUPPORTED_PROVIDERS, strict: true),
            404,
            'Provider tidak didukung.'
        );
    }
}
