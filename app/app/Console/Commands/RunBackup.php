<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Models\Tenant;
use App\Services\BackupService;
use Illuminate\Console\Command;

class RunBackup extends Command
{
    protected $signature = 'backup:run
        {--scope=platform : platform | tenant}
        {--tenant= : UUID tenant (wajib untuk scope tenant)}';

    protected $description = 'Jalankan backup manual/terjadwal (platform atau per tenant).';

    public function handle(BackupService $backup): int
    {
        $scope = $this->option('scope');
        $tenantId = $this->option('tenant') ?: null;

        if ($scope === Backup::SCOPE_TENANT && ! $tenantId) {
            $this->error('Scope tenant memerlukan opsi --tenant (UUID).');
            return self::FAILURE;
        }

        if ($tenantId && ! Tenant::withoutGlobalScopes()->find($tenantId)) {
            $this->error('Tenant tidak ditemukan.');
            return self::FAILURE;
        }

        $backup->run($scope, $tenantId, Backup::TYPE_FULL);

        $this->info('Backup selesai: scope=' . $scope . ' tenant=' . ($tenantId ?? 'platform'));

        return self::SUCCESS;
    }
}