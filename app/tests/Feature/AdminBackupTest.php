<?php

namespace Tests\Feature;

use App\Models\Backup;
use App\Models\Program;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminBackupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed();
    }

    protected function tenantId(): string
    {
        return Tenant::where('subdomain', 'kerkomit')->firstOrFail()->id;
    }

    protected function loginAdmin(): void
    {
        $this->actingAs(User::where('email', 'admin@kerkomit.test')->firstOrFail());
    }

    protected function secondTenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Yayasan Lain',
            'subdomain' => 'lain-backup',
            'category' => 'pendidikan',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_view_backups_page(): void
    {
        $this->loginAdmin();

        $this->get('/admin/backups')->assertStatus(200)->assertSee('Backup');
    }

    public function test_admin_can_run_manual_backup(): void
    {
        $this->loginAdmin();

        $this->post('/admin/backups')->assertRedirect();

        $this->assertDatabaseHas('backups', [
            'tenant_id' => $this->tenantId(),
            'scope' => 'tenant',
            'status' => 'success',
        ]);
    }

    public function test_backup_contains_tenant_data(): void
    {
        $admin = User::where('email', 'admin@kerkomit.test')->firstOrFail();
        $program = Program::create([
            'tenant_id' => $this->tenantId(),
            'title' => 'Backup Catch',
            'slug' => 'backup-catch-' . uniqid(),
            'content' => 'Konten',
            'status' => 'ongoing',
        ]);

        $backup = app(BackupService::class)->run('tenant', $this->tenantId(), 'full', $admin->id);

        $content = Storage::disk('local')->get($backup->file_path);
        $payload = json_decode($content, true);

        $this->assertSame('tenant', $payload['meta']['scope']);
        $this->assertArrayHasKey('programs', $payload['tables']);
        $this->assertTrue(
            collect($payload['tables']['programs'])->contains(fn ($row) => $row['id'] === $program->id)
        );
    }

    public function test_restore_restores_deleted_tenant_data(): void
    {
        $admin = User::where('email', 'admin@kerkomit.test')->firstOrFail();
        $program = Program::create([
            'tenant_id' => $this->tenantId(),
            'title' => 'Restore Catch',
            'slug' => 'restore-catch-' . uniqid(),
            'content' => 'Konten',
            'status' => 'ongoing',
        ]);

        $backup = app(BackupService::class)->run('tenant', $this->tenantId(), 'full', $admin->id);

        $program->forceDelete();
        $this->assertDatabaseMissing('programs', ['id' => $program->id]);

        app(BackupService::class)->restore($backup);

        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'title' => 'Restore Catch',
        ]);
    }

    public function test_backup_file_can_be_downloaded(): void
    {
        $admin = User::where('email', 'admin@kerkomit.test')->firstOrFail();
        $backup = app(BackupService::class)->run('tenant', $this->tenantId(), 'full', $admin->id);

        $this->loginAdmin();

        $this->get(route('admin.backups.download', $backup))
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/json');
    }

    public function test_tenant_cannot_restore_other_tenant_backup(): void
    {
        $admin = User::where('email', 'admin@kerkomit.test')->firstOrFail();
        $backup = app(BackupService::class)->run('tenant', $this->tenantId(), 'full', $admin->id);

        $otherAdmin = User::create([
            'tenant_id' => $this->secondTenant()->id,
            'name' => 'Admin Lain',
            'email' => 'admin-lain@backup.test',
            'password' => Hash::make('secret1234'),
        ]);
        $this->actingAs($otherAdmin);

        $this->post(route('admin.backups.restore', $backup))->assertStatus(403);
        $this->delete(route('admin.backups.destroy', $backup))->assertStatus(403);
    }

    public function test_staff_cannot_access_backups(): void
    {
        $staff = User::create([
            'tenant_id' => $this->tenantId(),
            'name' => 'Staff Backup',
            'email' => 'staff-backup@kerkomit.test',
            'password' => Hash::make('secret1234'),
        ]);
        app(PermissionRegistrar::class)->setPermissionsTeamId($staff->tenant_id);
        $staff->assignRole('staff_yayasan');
        $this->actingAs($staff);

        $this->get('/admin/backups')->assertStatus(403);
        $this->post('/admin/backups')->assertStatus(403);
    }

    public function test_super_admin_can_run_platform_backup(): void
    {
        $this->actingAs(User::where('email', 'superadmin@system.test')->firstOrFail());

        $this->post('/admin/backups')->assertRedirect();

        $this->assertDatabaseHas('backups', [
            'tenant_id' => null,
            'scope' => 'platform',
            'status' => 'success',
        ]);
    }
}
