<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CaptureUtm — menangkap parameter utm_* dari URL dan menyimpannya ke session.
 *
 * Saat pengunjung mendarat lewat short link / iklan dengan UTM, nilai ini
 * dipertahankan di session sehingga donasi berikutnya tetap teratribusi
 * (flow atribusi Modul 04 — Campaign).
 */
class CaptureUtm
{
    public const SESSION_KEY = 'utm';

    protected array $fields = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];

    public function handle(Request $request, Closure $next): Response
    {
        $hasUtm = false;
        $data = [];

        foreach ($this->fields as $field) {
            $value = $request->query($field);
            if ($value !== null && $value !== '') {
                $data[$field] = $value;
                $hasUtm = true;
            }
        }

        if ($hasUtm) {
            $request->session()->put(self::SESSION_KEY, $data);
        }

        return $next($request);
    }
}