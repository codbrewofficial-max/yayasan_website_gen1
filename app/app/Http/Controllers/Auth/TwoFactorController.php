<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class TwoFactorController extends Controller
{
    protected function pendingUser(Request $request): ?User
    {
        $id = $request->session()->get('two_factor:user:id');
        return $id ? User::find($id) : null;
    }

    public function challenge(): View|RedirectResponse
    {
        // Tampilkan challenge untuk user yang menunggu 2FA di login
        return session()->has('two_factor:user:id')
            ? view('auth.two-factor-challenge')
            : redirect()->route('login');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login');
        }

        $code = str_replace(' ', '', $request->code);

        if (! $this->validCode($user, $code) && ! $this->validRecovery($user, $code)) {
            return back()->withErrors(['code' => 'Kode 2FA tidak valid.']);
        }

        $remember = (bool) $request->session()->get('two_factor:auth:remember');
        $request->session()->forget(['two_factor:user:id', 'two_factor:auth:remember']);
        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    protected function validCode(User $user, string $code): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }

        try {
            $secret = Crypt::decryptString($user->two_factor_secret);
        } catch (\Throwable) {
            return false;
        }

        return (bool) app('pragmarx.google2fa')->verifyKey($secret, $code);
    }

    protected function validRecovery(User $user, string $code): bool
    {
        if (! $user->two_factor_recovery_codes) {
            return false;
        }

        $codes = json_decode($user->two_factor_recovery_codes, true) ?? [];

        foreach ($codes as $i => $candidate) {
            if (hash_equals($candidate, $code)) {
                unset($codes[$i]);
                $user->forceFill([
                    'two_factor_recovery_codes' => json_encode(array_values($codes)),
                ])->save();
                return true;
            }
        }

        return false;
    }
}