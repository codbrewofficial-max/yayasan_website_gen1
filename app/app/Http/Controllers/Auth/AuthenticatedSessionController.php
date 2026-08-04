<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Auth::attempt($credentials)) {
            return back()->withErrors(['email' => 'Kredensial tidak sesuai.']);
        }

        if (! $user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Akun dinonaktifkan.']);
        }

        if ($user->two_factor_secret) {
            $request->session()->put('two_factor:user:id', $user->id);
            $request->session()->put('two_factor:auth:remember', $request->boolean('remember'));
            Auth::logout();
            return redirect()->route('two-factor.challenge');
        }

        $request->session()->regenerate();
        $request->session()->put('two_factor:auth:remember', $request->boolean('remember'));

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
