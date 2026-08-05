<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Donation;
use App\Services\DonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function __construct(protected DonationService $donationService)
    {
    }

    public function index(Request $request): View
    {
        $donations = Donation::query()
            ->with(['campaign'])
            ->when($request->filled('status'), fn ($q) => $q->where('payment_status', $request->status))
            ->when($request->filled('campaign_id'), fn ($q) => $q->where('campaign_id', $request->campaign_id))
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($w) use ($request) {
                    $w->where('order_id', 'like', '%' . $request->q . '%')
                        ->orWhere('donor_name', 'like', '%' . $request->q . '%')
                        ->orWhere('donor_email', 'like', '%' . $request->q . '%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $campaigns = Campaign::query()->orderBy('title')->get();

        return view('admin.donations.index', compact('donations', 'campaigns'));
    }

    public function show(Donation $donation): View
    {
        $donation->load(['campaign', 'user', 'campaignLink', 'pageVisit']);

        return view('admin.donations.show', compact('donation'));
    }

    public function updateStatus(Request $request, Donation $donation): RedirectResponse
    {
        $data = $request->validate([
            'payment_status' => ['required', 'in:' . implode(',', Donation::STATUSES)],
        ]);

        $this->donationService->setStatusByAdmin($donation, $data['payment_status']);

        return back()->with('success', 'Status donasi diperbarui.');
    }
}