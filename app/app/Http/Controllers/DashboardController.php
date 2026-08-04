<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Program;
use App\Support\TenantContext;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(TenantContext $tenantContext): View
    {
        $tenant = $tenantContext->get();

        $stats = [
            'programs' => Program::query()->count(),
            'campaigns' => Campaign::query()->count(),
            'donations' => Donation::query()->count(),
            'collected' => (float) Donation::query()
                ->where('payment_status', Donation::STATUS_PAID)
                ->sum('amount'),
        ];

        $recentDonations = Donation::query()
            ->with('campaign')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('tenant', 'stats', 'recentDonations'));
    }
}