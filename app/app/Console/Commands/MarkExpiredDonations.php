<?php

namespace App\Console\Commands;

use App\Models\Donation;
use Illuminate\Console\Command;

class MarkExpiredDonations extends Command
{
    protected $signature = 'donations:expire';

    protected $description = 'Tandai donasi pending yang melewati batas waktu pembayaran sebagai expired.';

    public function handle(): int
    {
        $cutoff = now()->subMinutes((int) config('payment.midtrans.expiry.duration', 30));

        $count = Donation::query()
            ->withoutTenantScope()
            ->where('payment_status', Donation::STATUS_PENDING)
            ->where('created_at', '<', $cutoff)
            ->update(['payment_status' => Donation::STATUS_EXPIRED]);

        $this->info("{$count} donasi ditandai expired.");

        return self::SUCCESS;
    }
}