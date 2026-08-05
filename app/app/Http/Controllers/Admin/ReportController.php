<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Lead;
use App\Models\PageVisit;
use App\Models\Program;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request, TenantContext $tenantContext): View
    {
        abort_unless($tenantContext->has(), 403, 'Pilih tenant terlebih dahulu.');

        $from = $request->input('from');
        $to = $request->input('to');

        $donations = Donation::query()->with('campaign.program');

        if ($from) {
            $donations->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $donations->whereDate('created_at', '<=', $to);
        }

        $donations = $donations->get();
        $paid = $donations->where('payment_status', Donation::STATUS_PAID)->values();

        $pageVisits = PageVisit::query();
        if ($from) {
            $pageVisits->whereDate('visited_at', '>=', $from);
        }
        if ($to) {
            $pageVisits->whereDate('visited_at', '<=', $to);
        }
        $pageVisits = $pageVisits->get();

        $stats = [
            'programs' => Program::query()->count(),
            'campaigns' => Campaign::query()->count(),
            'donations' => $donations->count(),
            'paid_donations' => $paid->count(),
            'collected' => (float) $paid->sum('amount'),
            'unique_donors' => $paid->pluck('donor_email')->filter()->unique()->count(),
            'leads' => Lead::query()->count(),
            'visits' => $pageVisits->count(),
        ];

        $donationsByCampaign = $donations
            ->groupBy(fn ($d) => $d->campaign_id ?: 'general')
            ->map(function ($group) {
                $campaign = $group->first()->campaign;
                $paidGroup = $group->where('payment_status', Donation::STATUS_PAID);

                return [
                    'name' => $campaign?->title ?? 'Umum',
                    'count' => $group->count(),
                    'paid_count' => $paidGroup->count(),
                    'total' => (float) $paidGroup->sum('amount'),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $monthlyTrend = collect();
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthPaid = $paid->filter(fn ($d) => $d->created_at->format('Y-m') === $month);

            $monthlyTrend->push([
                'month' => now()->subMonths($i)->translatedFormat('M Y'),
                'count' => $monthPaid->count(),
                'total' => (float) $monthPaid->sum('amount'),
            ]);
        }

        $visitsByDevice = $pageVisits->groupBy(fn ($v) => $v->device_type ?: 'unknown')
            ->map->count();

        $topPages = $pageVisits->groupBy('page_url')->map->count()
            ->sortByDesc(fn ($c) => $c)
            ->take(10);

        $leadByStatus = Lead::query()->selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        return view('admin.reports.index', compact(
            'stats',
            'donationsByCampaign',
            'monthlyTrend',
            'visitsByDevice',
            'topPages',
            'leadByStatus',
            'from',
            'to',
        ));
    }
}