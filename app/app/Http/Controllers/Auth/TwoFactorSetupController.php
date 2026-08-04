<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TwoFactorSetupController extends Controller
{
    protected function google2fa()
    {
        return app('pragmarx.google2fa');
    }

    public function show(Request $request): View
    {
        $user = $request->user();

        if (! $user->two_factor_secret) {
            $secret = $this->google2fa()->generateSecretKey();
            session()->put('two_factor:setup:secret', $secret);
        } else {
            $secret = Crypt::decryptString($user->two_factor_secret);
        }

        $qr = $this->google2fa()->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return view('auth.two-factor-setup', [
            'user' => $user,
            'secret' => $secret,
            'qr' => $qr,
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $user = $request->user();
        $secret = session()->get('two_factor:setup:secret');

        if (! $secret || ! $this->google2fa()->verifyKey($secret, str_replace(' ', '', $request->code))) {
            return back()->withErrors(['code' => 'Kode tidak valid.']);
        }

        $recoveryGenerator = function () {
            $codes = [];
            for ($i = 0; $i < 8; $i++) {
                $codes[] = Str::random(10);
            }
            return $codes;
        };

        $user->forceFill([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => json_encode($recoveryGenerator()),
        ])->save();

        session()->forget('two_factor:setup:secret');

        return redirect()->route('two-factor.setup')->with('status', '2FA diaktifkan.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        return back()->with('status', '2FA dinonaktifkan.');
    }
}