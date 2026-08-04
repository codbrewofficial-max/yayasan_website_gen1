<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Mail\LeadContactMail;
use App\Models\Lead;
use App\Services\TemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function __construct(protected TemplateService $templateService)
    {
    }

    public function show(): View
    {
        $seo = [
            'title' => 'Hubungi Kami',
            'description' => 'Hubungi yayasan melalui email atau WhatsApp.',
            'canonical' => route('public.contact'),
            'type' => 'website',
        ];

        return view($this->templateService->baseView('contact'), compact('seo'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'lead_type' => ['required', 'in:' . Lead::TYPE_EMAIL . ',' . Lead::TYPE_WHATSAPP],
        ]);

        $tenant = app(\App\Support\TenantContext::class)->requireId();

        $lead = Lead::create([
            'tenant_id' => $tenant,
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'lead_type' => $data['lead_type'],
            'status' => Lead::STATUS_NEW,
        ]);

        if ($data['lead_type'] === Lead::TYPE_EMAIL) {
            $this->sendToYayasan($request, $lead);

            return back()->with('success', 'Pesan Anda telah terkirim.');
        }

        return redirect()->away($this->whatsappUrl($request, $lead));
    }

    protected function sendToYayasan(Request $request, Lead $lead): void
    {
        $tenant = app(\App\Support\TenantContext::class)->get();
        $contactEmail = $tenant?->contact_email ?: config('mail.from.address');

        Mail::to($contactEmail)->send(new LeadContactMail($lead));
    }

    protected function whatsappUrl(Request $request, Lead $lead): string
    {
        $tenant = app(\App\Support\TenantContext::class)->get();
        $phone = preg_replace('/[^0-9]/', '', $tenant?->contact_phone ?? '');

        $message = "Halo, saya {$lead->name}." . PHP_EOL;
        if ($lead->subject) {
            $message .= "Topik: {$lead->subject}" . PHP_EOL;
        }
        $message .= "Pesan: {$lead->message}";

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
    }
}