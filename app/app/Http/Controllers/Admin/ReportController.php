<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Article;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Lead;
use App\Models\PageVisit;
use App\Models\Program;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request, TenantContext $tenantContext): View
    {
        $platform = ! $tenantContext->has();

        $from = $request->input('from');
        $to = $request->input('to');

        $donationsQ = Donation::query()->with('campaign.program');
        $visitsQ = PageVisit::query();
        $programsQ = Program::query();
        $campaignsQ = Campaign::query();
        $leadsQ = Lead::query();

        if ($platform) {
            $donationsQ->withoutTenantScope();
            $visitsQ->withoutTenantScope();
            $programsQ->withoutTenantScope();
            $campaignsQ->withoutTenantScope();
            $leadsQ->withoutTenantScope();
        }

        if ($from) {
            $donationsQ->whereDate('created_at', '>=', $from);
            $visitsQ->whereDate('visited_at', '>=', $from);
        }
        if ($to) {
            $donationsQ->whereDate('created_at', '<=', $to);
            $visitsQ->whereDate('visited_at', '<=', $to);
        }

        $donations = $donationsQ->get();
        $paid = $donations->where('payment_status', Donation::STATUS_PAID)->values();
        $pageVisits = $visitsQ->get();

        $stats = [
            'tenants' => $platform ? Tenant::query()->withoutGlobalScopes()->count() : 1,
            'programs' => $programsQ->count(),
            'campaigns' => $campaignsQ->count(),
            'donations' => $donations->count(),
            'paid_donations' => $paid->count(),
            'collected' => (float) $paid->sum('amount'),
            'unique_donors' => $paid->pluck('donor_email')->filter()->unique()->count(),
            'leads' => $leadsQ->count(),
            'visits' => $pageVisits->count(),
            'content_views' => $this->contentViews($platform),
        ];

        // Funnel konversi: kunjungan → donasi dibuat → dibayar sukses.
        $funnel = [
            'visits' => $stats['visits'],
            'donations' => $stats['donations'],
            'paid' => $stats['paid_donations'],
            'visit_to_donation' => $this->rate($stats['donations'], $stats['visits']),
            'donation_to_paid' => $this->rate($stats['paid_donations'], $stats['donations']),
            'visit_to_paid' => $this->rate($stats['paid_donations'], $stats['visits']),
        ];

        $donationsByCampaign = $this->donationsByCampaign($donations);
        $monthlyTrend = $this->monthlyTrend($paid);
        $visitsByDevice = $pageVisits->groupBy(fn ($v) => $v->device_type ?: 'unknown')->map->count();
        $topPages = $pageVisits->groupBy('page_url')->map->count()->sortByDesc(fn ($c) => $c)->take(10);
        $leadByStatus = Lead::query()->when($platform, fn ($q) => $q->withoutTenantScope())
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        $byTenant = $platform ? $this->byTenant($donations) : collect();
        $byChannel = $this->byChannel($paid);
        $byMethod = $platform ? collect() : $paid->groupBy(fn ($d) => $d->payment_method ?: 'belum_tercatat')->map->count();

        return view('admin.reports.index', compact(
            'stats',
            'platform',
            'funnel',
            'donationsByCampaign',
            'monthlyTrend',
            'visitsByDevice',
            'topPages',
            'leadByStatus',
            'byTenant',
            'byChannel',
            'byMethod',
            'from',
            'to',
        ));
    }

    protected function donationsByCampaign(Collection $donations): Collection
    {
        return $donations
            ->groupBy(fn ($d) => $d->campaign_id ?: 'general')
            ->map(function ($group) {
                $campaign = $group->first()->campaign;
                $paidGroup = $group->where('payment_status', Donation::STATUS_PAID);

                return [
                    'name' => $campaign?->title ?? 'Umum',
                    'count' => $group->count(),
                    'paid_count' => $paidGroup->count(),
                    'total' => (float) $paidGroup->sum('amount'),
                    'target' => (float) ($campaign?->target_amount ?? 0),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    protected function monthlyTrend(Collection $paid): Collection
    {
        $trend = collect();

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthPaid = $paid->filter(fn ($d) => $d->created_at->format('Y-m') === $month);

            $trend->push([
                'month' => now()->subMonths($i)->translatedFormat('M Y'),
                'count' => $monthPaid->count(),
                'total' => (float) $monthPaid->sum('amount'),
            ]);
        }

        return $trend;
    }

    protected function byTenant(Collection $donations): Collection
    {
        return $donations
            ->groupBy('tenant_id')
            ->map(function ($group) {
                $tenant = Tenant::query()->withoutGlobalScopes()->find($group->first()->tenant_id);
                $paidGroup = $group->where('payment_status', Donation::STATUS_PAID);

                return [
                    'name' => $tenant?->name ?? 'Unknown',
                    'count' => $group->count(),
                    'paid_count' => $paidGroup->count(),
                    'total' => (float) $paidGroup->sum('amount'),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    protected function byChannel(Collection $paid): Collection
    {
        return $paid
            ->groupBy(fn ($d) => $d->utm_source ?: 'direct')
            ->map(function ($group, $source) {
                return [
                    'source' => $source,
                    'count' => $group->count(),
                    'total' => (float) $group->sum('amount'),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    protected function contentViews(bool $platform): int
    {
        if ($platform) {
            return (int) Program::query()->withoutTenantScope()->sum('views_count')
                + (int) Article::query()->withoutTenantScope()->sum('views_count')
                + (int) Album::query()->withoutTenantScope()->sum('views_count');
        }

        return (int) Program::query()->sum('views_count')
            + (int) Article::query()->sum('views_count')
            + (int) Album::query()->sum('views_count');
    }

    protected function rate(int $part, int $whole): float
    {
        return $whole > 0 ? round(($part / $whole) * 100, 1) : 0.0;
    }
}
