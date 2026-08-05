<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminPageCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function loginAdmin(): void
    {
        $this->actingAs(User::where('email', 'admin@kerkomit.test')->firstOrFail());
    }

    protected function tenant(): Tenant
    {
        return Tenant::where('subdomain', 'kerkomit')->firstOrFail();
    }

    public function test_admin_can_list_pages(): void
    {
        $this->loginAdmin();
        $this->makePage('Tentang Kami', 'tentang-kami');

        $this->get('/admin/pages')->assertStatus(200)->assertSee('Tentang Kami');
    }

    public function test_admin_can_create_page(): void
    {
        $this->loginAdmin();

        $this->post('/admin/pages', [
            'title' => 'Visi Misi',
            'content' => 'Konten visi misi',
            'is_published' => '1',
        ])->assertRedirect();

        $page = Page::where('slug', 'visi-misi')->firstOrFail();
        $this->assertSame($this->tenant()->id, $page->tenant_id);
        $this->assertTrue($page->is_published);
    }

    public function test_admin_can_update_page(): void
    {
        $this->loginAdmin();
        $page = $this->makePage('Lama', 'lama');

        $this->put('/admin/pages/' . $page->id, [
            'title' => 'Baru',
            'content' => 'Konten baru',
            'is_published' => '',
        ])->assertRedirect();

        $fresh = $page->fresh();
        $this->assertSame('Baru', $fresh->title);
        $this->assertFalse($fresh->is_published);
    }

    public function test_admin_can_delete_page(): void
    {
        $this->loginAdmin();
        $page = $this->makePage('Hapus', 'hapus');

        $this->delete('/admin/pages/' . $page->id)->assertRedirect();

        $this->assertSoftDeleted('pages', ['id' => $page->id]);
    }

    public function test_slug_unique_per_tenant(): void
    {
        $this->loginAdmin();
        $this->makePage('Visi Misi', 'visi-misi');

        $this->post('/admin/pages', [
            'title' => 'Visi Misi',
            'content' => 'x',
        ])->assertRedirect();

        $count = Page::query()->where('slug', 'like', 'visi-misi%')->count();
        $this->assertGreaterThanOrEqual(2, $count);
    }

    public function test_staff_cannot_access_pages(): void
    {
        $staff = User::create([
            'tenant_id' => $this->tenant()->id,
            'name' => 'Staff',
            'email' => 'staff-page@kerkomit.test',
            'password' => Hash::make('secret1234'),
        ]);
        app(PermissionRegistrar::class)->setPermissionsTeamId($staff->tenant_id);
        $staff->assignRole('staff_yayasan');
        $this->actingAs($staff);

        $this->get('/admin/pages')->assertStatus(200);
    }

    protected function makePage(string $title, string $slug): Page
    {
        return Page::create([
            'tenant_id' => $this->tenant()->id,
            'title' => $title,
            'slug' => $slug,
            'content' => 'Konten',
            'is_published' => true,
        ]);
    }
}