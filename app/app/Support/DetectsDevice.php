<?php

namespace App\Support;

use Illuminate\Http\Request;

trait DetectsDevice
{
    public function detectDevice(Request $request): string
    {
        $ua = strtolower($request->userAgent() ?? '');

        if (str_contains($ua, 'ipad') || (str_contains($ua, 'android') && ! str_contains($ua, 'mobile'))) {
            return 'tablet';
        }

        if (preg_match('/mobile|android|iphone|ipod/i', $ua)) {
            return 'mobile';
        }

        return 'desktop';
    }
}