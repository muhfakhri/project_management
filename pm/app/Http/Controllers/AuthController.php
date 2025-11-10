<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class AuthController extends Controller
{
    // Show login form
    public function showLoginForm()
    {
        // Redirect to dashboard if already authenticated
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    // Handle login post
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    // Logout user
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    // Show registration form
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    // Handle registration
    public function register(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        // Generate unique username from email
        $baseUsername = Str::slug(strstr($data['email'], '@', true), '_');
        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . '_' . $counter;
            $counter++;
        }

        $user = User::create([
            'username' => $username,
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);
        return redirect()->route('dashboard');
    }

    // Show password reset request form
    public function showForgotForm()
    {
        return view('auth.passwords.email');
    }

    // Send password reset link
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => ['required','email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }

    // Show reset form
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with(['token' => $token, 'email' => $request->email]);
    }

    // Handle resetting the password
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    }

    // Google OAuth redirect
    public function redirectToGoogle()
    {
        // Basic validation to avoid Google returning 400 invalid_request when client_id is missing
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUrl = config('services.google.redirect');

        if (empty($clientId) || empty($clientSecret)) {
            $msg = 'Akses diblokir: Missing required Google OAuth configuration (client_id or client_secret).';
            $msg .= ' Please set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in your .env and run: php artisan config:clear';
            return redirect()->route('login')->withErrors(['oauth' => $msg]);
        }

        if (empty($redirectUrl)) {
            $msg = 'Akses diblokir: Missing Google redirect URI configuration. Set GOOGLE_REDIRECT in .env or configure services.php.';
            return redirect()->route('login')->withErrors(['oauth' => $msg]);
        }

        return Socialite::driver('google')->redirect();
    }

    // Google OAuth callback
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['oauth' => 'Unable to login using Google.']);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            // Generate unique username from email
            $baseUsername = Str::slug(strstr($googleUser->getEmail(), '@', true), '_');
            $username = $baseUsername;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . '_' . $counter;
                $counter++;
            }

            $user = User::create([
                'username' => $username,
                'full_name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Google User',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => Hash::make(Str::random(24)),
            ]);
        } else {
            // Save google_id if not set
            if (empty($user->google_id)) {
                $user->google_id = $googleUser->getId();
                $user->save();
            }
        }

        Auth::login($user, true);
        return redirect()->route('dashboard');
    }
}