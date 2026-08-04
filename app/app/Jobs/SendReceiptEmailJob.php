<?php

namespace App\Jobs;

use App\Models\Donation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Kirim e-receipt donasi setelah pembayaran berhasil.
 *
 * Saat ini hanya mencatat log (belum ada Mail template/event GA4 di Modul ini).
 * Alur selanjutnya: render PDF → kirim email → fire event donation_completed (GA4).
 */
class SendReceiptEmailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Donation $donation,
    ) {
    }

    public function handle(): void
    {
        if (! $this->donation->isPaid()) {
            Log::warning('SendReceiptEmailJob: donasi belum paid, dibatalkan.', [
                'donation_id' => $this->donation->id,
                'status' => $this->donation->payment_status,
            ]);

            return;
        }

        Log::info('SendReceiptEmailJob: e-receipt dikirim (stub).', [
            'donation_id' => $this->donation->id,
            'order_id' => $this->donation->order_id,
            'to' => $this->donation->donor_email,
            'amount' => $this->donation->amount,
        ]);
    }
}