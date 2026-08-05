<?php

namespace App\Services;

use App\Models\Backup;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class BackupService
{
    protected string $disk = 'local';

    protected const TABLES = [
        'programs',
        'campaigns',
        'articles',
        'albums',
        'galleries',
        'members',
        'donations',
        'campaign_links',
        'pages',
        'settings',
        'leads',
        'media',
    ];

    /**
     * Backup tenant/beberapa tenant/platform dan catat statusnya.
     */
    public function run(string $scope, ?string $tenantId = null, string $type = Backup::TYPE_FULL, ?string $triggeredBy = null): Backup
    {
        $backup = Backup::create([
            'tenant_id' => $tenantId,
            'type' => $type,
            'scope' => $scope,
            'triggered_by' => $triggeredBy,
            'status' => Backup::STATUS_IN_PROGRESS,
        ]);

        try {
            $result = $this->build($scope, $tenantId);

            $backup->forceFill([
                'status' => Backup::STATUS_SUCCESS,
                'file_name' => $result['file_name'],
                'file_path' => $result['file_path'],
                'file_size' => $result['file_size'],
                'completed_at' => now(),
            ])->save();
        } catch (\Throwable $e) {
            $backup->forceFill([
                'status' => Backup::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ])->save();

            throw $e;
        }

        return $backup->fresh();
    }

    /**
     * Kembalikan data per-tenant dari file backup (upsert by id, idempotent).
     */
    public function restore(Backup $backup): void
    {
        if (! $backup->isSuccessful() || ! $backup->file_path) {
            throw new RuntimeException('Backup belum berhasil / tidak memiliki file.');
        }

        $payload = json_decode($this->read($backup), true);

        if (! is_array($payload) || ! isset($payload['tables'])) {
            throw new RuntimeException('File backup tidak valid.');
        }

        DB::transaction(function () use ($payload) {
            foreach ($payload['tables'] as $table => $rows) {
                foreach ($rows as $row) {
                    DB::table($table)->updateOrInsert(['id' => $row['id']], $row);
                }
            }
        });
    }

    public function downloadFileInfo(Backup $backup): array
    {
        return [
            'path' => $backup->file_path,
            'name' => $backup->file_name ?? 'backup.json',
            'disk' => $this->disk,
        ];
    }

    protected function build(string $scope, ?string $tenantId): array
    {
        $dump = $this->databaseDumpJson($scope, $tenantId);

        $folder = $scope === Backup::SCOPE_PLATFORM ? 'platform' : 'tenant-' . $tenantId;
        $filePath = 'backups/' . $folder . '/' . now()->format('Y-m-d-His') . '.json';

        Storage::disk($this->disk)->put($filePath, $dump);

        return [
            'file_name' => basename($filePath),
            'file_path' => $filePath,
            'file_size' => Storage::disk($this->disk)->size($filePath),
        ];
    }

    protected function databaseDumpJson(string $scope, ?string $tenantId): string
    {
        $tables = self::TABLES;

        if ($scope === Backup::SCOPE_PLATFORM) {
            $tables = array_merge(['tenants', 'users'], $tables);
        }

        $payload = [
            'meta' => [
                'scope' => $scope,
                'tenant_id' => $tenantId,
                'created_at' => now()->toISOString(),
                'app_name' => config('app.name'),
            ],
            'tables' => [],
        ];

        foreach ($tables as $table) {
            $query = DB::table($table);

            if ($scope === Backup::SCOPE_TENANT && $tenantId !== null) {
                $query->where('tenant_id', $tenantId);
            }

            $payload['tables'][$table] = $query->get()
                ->map(fn ($row) => (array) $row)
                ->all();
        }

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    protected function read(Backup $backup): string
    {
        if (! Storage::disk($this->disk)->exists($backup->file_path)) {
            throw new RuntimeException('File backup tidak ditemukan.');
        }

        return Storage::disk($this->disk)->get($backup->file_path);
    }
}