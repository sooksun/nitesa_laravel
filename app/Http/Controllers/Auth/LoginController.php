<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle(): RedirectResponse
    {
        /** @var \Symfony\Component\HttpFoundation\RedirectResponse $response */
        $response = Socialite::driver('google')->redirect();

        return redirect()->away($response->getTargetUrl());
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            /** @var SocialiteUser $googleUser */
            $googleUser = Socialite::driver('google')->user();

            $googleId = $googleUser->getId();
            $googleEmail = $googleUser->getEmail();
            $googleName = $googleUser->getName();
            $googleAvatar = $googleUser->getAvatar();

            // Find existing user by google_id or email
            $user = User::where('googleId', $googleId)
                        ->orWhere('email', $googleEmail)
                        ->first();

            if ($user) {
                // Update google_id if not set
                if (empty($user->googleId)) {
                    $user->googleId = $googleId;
                    $user->avatar = $googleAvatar;
                    $user->save();
                }
            } else {
                // Create new user
                $user = User::create([
                    'id' => (string) Str::ulid(),
                    'name' => $googleName,
                    'email' => $googleEmail,
                    'googleId' => $googleId,
                    'avatar' => $googleAvatar,
                    'password' => Hash::make(Str::random(24)), // Random password for Google users
                    'role' => Role::SCHOOL, // Default role for new Google users
                    'isActive' => true,
                ]);
            }

            // Check if user is active
            if (! $user->isActive) {
                return redirect()->route('login')
                    ->with('error', 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ');
            }

            // Login the user
            Auth::login($user, true);

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'ไม่สามารถเข้าสู่ระบบด้วย Google ได้: ' . $e->getMessage());
        }
    }
}
