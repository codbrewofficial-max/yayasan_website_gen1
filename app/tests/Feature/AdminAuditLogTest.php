<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Program;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminAuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
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

    protected function storeProgram(array $overrides = []): Program
    {
        return Program::create(array_merge([
            'tenant_id' => $this->tenantId(),
            'title' => 'Audit Program',
            'slug' => 'audit-program-' . uniqid(),
            'content' => 'Konten',
            'status' => 'ongoing',
        ], $overrides));
    }

    public function test_admin_can_view_audit_log_page(): void
    {
        $this->loginAdmin();

        $this->get('/admin/audit-logs')->assertStatus(200)->assertSee('Audit Log');
    }

    public function test_creating_program_records_audit_log(): void
    {
        $this->loginAdmin();
        $admin = User::where('email', 'admin@kerkomit.test')->firstOrFail();

        $program = $this->storeProgram();

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $this->tenantId(),
            'user_id' => $admin->id,
            'model_type' => Program::class,
            'model_id' => $program->id,
            'action' => 'create',
        ]);
    }

    public function test_updating_program_records_audit_log_with_old_and_new_values(): void
    {
        $this->loginAdmin();
        $program = $this->storeProgram();

        $program->update(['title' => 'Audit Program Baru']);

        $log = AuditLog::query()
            ->where('model_type', Program::class)
            ->where('model_id', $program->id)
            ->where('action', 'update')
            ->latest('created_at')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('Audit Program', $log->old_values['title']);
        $this->assertSame('Audit Program Baru', $log->new_values['title']);
    }

    public function test_deleting_program_records_audit_log(): void
    {
        $this->loginAdmin();
        $program = $this->storeProgram();

        $program->delete();

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Program::class,
            'model_id' => $program->id,
            'action' => 'delete',
        ]);
    }

    public function test_login_records_audit_log(): void
    {
        $this->post('/login', [
            'email' => 'admin@kerkomit.test',
            'password' => 'password',
        ])->assertStatus(302);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => User::where('email', 'admin@kerkomit.test')->firstOrFail()->id,
            'action' => 'login',
        ]);
    }

    public function test_audit_logs_are_scoped_per_tenant(): void
    {
        $other = Tenant::create([
            'name' => 'Yayasan Kedua',
            'subdomain' => 'kedua-audit',
            'category' => 'pendidikan',
            'status' => 'active',
        ]);

        $this->storeProgram(['tenant_id' => $other->id, 'title' => 'Program Tenant Kedua']);

        $this->loginAdmin();

        $response = $this->get('/admin/audit-logs');

        $response->assertStatus(200);
        $response->assertDontSee('Program Tenant Kedua');
    }

    public function test_super_admin_sees_all_tenant_logs(): void
    {
        $other = Tenant::create([
            'name' => 'Yayasan Ketiga',
            'subdomain' => 'ketiga-audit',
            'category' => 'pendidikan',
            'status' => 'active',
        ]);

        $this->storeProgram(['tenant_id' => $other->id, 'title' => 'Program Yayasan Ketiga']);

        $this->actingAs(User::where('email', 'superadmin@system.test')->firstOrFail());

        $this->get('/admin/audit-logs')->assertStatus(200)->assertSee('Program Yayasan Ketiga');
    }

    public function test_staff_cannot_view_audit_log(): void
    {
        $staff = User::create([
            'tenant_id' => $this->tenantId(),
            'name' => 'Staff Audit',
            'email' => 'staff-audit@kerkomit.test',
            'password' => Hash::make('secret1234'),
        ]);
        app(PermissionRegistrar::class)->setPermissionsTeamId($staff->tenant_id);
        $staff->assignRole('staff_yayasan');
        $this->actingAs($staff);

        $this->get('/admin/audit-logs')->assertStatus(403);
    }
}
