<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function loginSubmit(LoginRequest $request)
    {
        $request->ensureIsNotRateLimited();

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $request->hitThrottle();

            throw ValidationException::withMessages([
                'username' => __('auth.failed'),
            ])->redirectTo(route('login'));
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'username' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ])->redirectTo(route('login'));
        }

        $request->clearThrottle();

        Auth::login($user, $request->boolean('remember'));

        $user->update(['last_login_at' => now()]);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Selamat datang, ' . e($user->nama) . '!');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil logout.');
    }
}
