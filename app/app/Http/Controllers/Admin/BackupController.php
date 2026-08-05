<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\BackupService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected BackupService $backupService,
    ) {
    }

    public function index(Request $request): View
    {
        $query = Backup::query()->with('triggerer');

        if ($this->tenantContext->has()) {
            $query->where('tenant_id', $this->tenantContext->id());
        } elseif ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->query('tenant_id'));
        }

        $backups = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.backups.index', compact('backups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $scope = $this->tenantContext->has() ? Backup::SCOPE_TENANT : Backup::SCOPE_PLATFORM;
        $tenantId = $this->tenantContext->id();

        try {
            $backup = $this->backupService->run($scope, $tenantId, Backup::TYPE_FULL, $request->user()->id);

            return back()->with('success', 'Backup selesai. ' . ($backup->file_name ?? ''));
        } catch (\Throwable $e) {
            return back()->withErrors(['backup' => 'Backup gagal: ' . $e->getMessage()]);
        }
    }

    public function download(Backup $backup): StreamedResponse
    {
        $this->authorizeBackup($backup);

        $info = $this->backupService->downloadFileInfo($backup);

        return Storage::disk($info['disk'])->download($info['path'], $info['name']);
    }

    public function restore(Backup $backup): RedirectResponse
    {
        $this->authorizeBackup($backup);

        try {
            $this->backupService->restore($backup);

            return back()->with('success', 'Data berhasil dipulihkan dari backup.');
        } catch (\Throwable $e) {
            return back()->withErrors(['restore' => 'Restore gagal: ' . $e->getMessage()]);
        }
    }

    public function destroy(Backup $backup): RedirectResponse
    {
        $this->authorizeBackup($backup);

        if ($backup->file_path && Storage::disk('local')->exists($backup->file_path)) {
            Storage::disk('local')->delete($backup->file_path);
        }

        $backup->delete();

        return back()->with('success', 'Backup dihapus.');
    }

    protected function authorizeBackup(Backup $backup): void
    {
        if (! $this->tenantContext->has()) {
            return;
        }

        abort_unless(
            $backup->scope === Backup::SCOPE_TENANT && $backup->tenant_id === $this->tenantContext->id(),
            403,
        );
    }
}