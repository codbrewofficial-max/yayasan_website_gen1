<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Tenant;
use App\Services\Payment\PaymentGateway;
use App\Support\TenantContext;
use Illuminate\Support\Str;

/**
 * DonationService — alur donasi inti.
 *
 * Buat order (donations pending) → panggil payment gateway → kembalikan snap token.
 * order_id ber-prefix tenant: "T{tenantShort}-{uuid}" agar webhook bisa resolve tenant.
 */
class DonationService
{
    public const MIN_AMOUNT = 10000;

    public function __construct(
        protected TenantContext $tenantContext,
        protected PaymentGateway $gateway,
    ) {
    }

    /**
     * Buat donasi baru.
     *
     * @param array{campaign: Campaign, donor_name: string, donor_email: string,
     *             donor_phone: string, amount: int|string, is_anonymous: bool,
     *             message: ?string, utm: array, user_id: ?string} $data
     * @return array{donation: Donation, token: ?string, redirect_url: ?string}
     */
    public function create(array $data): array
    {
        $campaign = $data['campaign'];
        $this->guardCampaign($campaign);

        $amount = (int) round((float) $data['amount']);
        if ($amount < self::MIN_AMOUNT) {
            throw new \InvalidArgumentException("Minimal donasi Rp " . number_format(self::MIN_AMOUNT, 0, ',', '.'));
        }

        $tenant = $this->tenantContext->requireId();
        $tenantModel = $this->tenantContext->get();

        $orderId = $this->buildOrderId($tenantModel);
        $utm = $data['utm'] ?? [];

        $donation = Donation::create([
            'tenant_id' => $tenant,
            'campaign_id' => $campaign->id,
            'user_id' => $data['user_id'] ?? null,
            'order_id' => $orderId,
            'donor_name' => $data['donor_name'],
            'donor_email' => $data['donor_email'],
            'donor_phone' => $data['donor_phone'],
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'amount' => $amount,
            'message' => $data['message'] ?? null,
            'donation_type' => Donation::TYPE_ONE_TIME,
            'payment_status' => Donation::STATUS_PENDING,
            'utm_source' => $utm['source'] ?? null,
            'utm_medium' => $utm['medium'] ?? null,
            'utm_campaign' => $utm['campaign'] ?? null,
            'utm_content' => $utm['content'] ?? null,
            'utm_term' => $utm['term'] ?? null,
            'campaign_link_id' => $data['campaign_link_id'] ?? null,
            'page_visit_id' => $data['page_visit_id'] ?? null,
        ]);

        $result = $this->gateway->createTransaction([
            'order_id' => $orderId,
            'gross_amount' => $amount,
            'customer' => [
                'first_name' => $donation->donor_name,
                'email' => $donation->donor_email,
                'phone' => $donation->donor_phone,
            ],
            'item' => [
                'id' => $campaign->slug,
                'price' => $amount,
                'quantity' => 1,
                'name' => Str::limit("Donasi: {$campaign->title}", 50),
            ],
        ]);

        if ($result['ref']) {
            $donation->forceFill(['payment_gateway_ref' => $result['ref']])->save();
        }

        return [
            'donation' => $donation,
            'token' => $result['token'],
            'redirect_url' => $result['redirect_url'],
        ];
    }

    /**
     * Mapping status Midtrans → internal, lalu proses.
     * Idempotent: donasi yang sudah paid tidak diproses ulang.
     */
    public function handleWebhook(array $payload): void
    {
        $orderId = $payload['order_id'] ?? null;
        if (! $orderId) {
            throw new \InvalidArgumentException('order_id tidak ada.');
        }

        $donation = Donation::query()
            ->withoutTenantScope()
            ->where('order_id', $orderId)
            ->first();

        if (! $donation) {
            throw new \RuntimeException('Donasi tidak ditemukan: ' . $orderId);
        }

        $this->applyStatus($donation, $this->mapStatus($payload['transaction_status'] ?? 'pending'), $payload['transaction_id'] ?? null);
    }

    protected function applyStatus(Donation $donation, string $status, ?string $transactionId = null): void
    {
        // Idempotent — hindari proses ganda saat webhook duplikat.
        if ($donation->isPaid()) {
            return;
        }

        $donation->forceFill([
            'payment_status' => $status,
            'payment_gateway_ref' => $transactionId ?: $donation->payment_gateway_ref,
        ]);

        if ($status === Donation::STATUS_PAID) {
            $donation->paid_at = now();
        }

        $donation->save();

        if ($status === Donation::STATUS_PAID) {
            $this->creditCampaign($donation);
            // e-receipt via queue; event GA4 donation_completed menyusul.
            \App\Jobs\SendReceiptEmailJob::dispatch($donation);
        }
    }

    protected function creditCampaign(Donation $donation): void
    {
        Campaign::query()
            ->withoutTenantScope()
            ->whereKey($donation->campaign_id)
            ->increment('collected_amount', (float) $donation->amount);
    }

    public function mapStatus(string $midtransStatus): string
    {
        return match ($midtransStatus) {
            'capture', 'settlement' => Donation::STATUS_PAID,
            'deny', 'cancel', 'expire' => Donation::STATUS_EXPIRED,
            'pending' => Donation::STATUS_PENDING,
            'refund', 'partial_refund' => Donation::STATUS_REFUNDED,
            default => Donation::STATUS_FAILED,
        };
    }

    protected function guardCampaign(Campaign $campaign): void
    {
        if ($campaign->status !== Campaign::STATUS_ACTIVE) {
            throw new \RuntimeException('Campaign tidak aktif.');
        }

        if ($campaign->end_date && $campaign->end_date->isPast()) {
            throw new \RuntimeException('Campaign telah berakhir.');
        }
    }

    protected function buildOrderId(?Tenant $tenant): string
    {
        $short = $tenant?->subdomain ? Str::upper($tenant->subdomain) : 'T';
        $short = Str::limit($short, 10, '');

        return "T{$short}-" . Str::uuid()->toString();
    }
}