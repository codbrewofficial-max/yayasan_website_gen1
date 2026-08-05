<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMemberCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function tearDown(): void
    {
        Storage::disk('public')->deleteDirectory('media');
        parent::tearDown();
    }

    protected function loginAs(string $email): void
    {
        $this->actingAs(User::where('email', $email)->firstOrFail());
    }

    protected function tenantId(): string
    {
        return \App\Models\Tenant::where('subdomain', 'kerkomit')->firstOrFail()->id;
    }

    public function test_admin_yayasan_can_list_members(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->get('/admin/members')->assertStatus(200)->assertSee('Pengurus');
    }

    public function test_admin_yayasan_can_create_member(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->post('/admin/members', [
            'name' => 'Budi Santoso',
            'group' => 'pengurus_inti',
            'position' => 'Ketua',
            'status' => 'active',
            'joined_at' => 2020,
            'bio' => 'Aktif berkegiatan.',
        ])->assertRedirect();

        $this->assertDatabaseHas('members', [
            'name' => 'Budi Santoso',
            'group' => 'pengurus_inti',
            'position' => 'Ketua',
            'status' => 'active',
            'joined_at' => 2020,
        ]);
    }

    public function test_admin_yayasan_can_update_member(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $member = $this->makeMember();

        $this->put('/admin/members/' . $member->id, [
            'name' => 'Budi Diperbarui',
            'group' => 'pembina',
            'position' => 'Penasihat',
            'status' => 'inactive',
        ])->assertRedirect();

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'name' => 'Budi Diperbarui',
            'group' => 'pembina',
            'status' => 'inactive',
        ]);
    }

    public function test_admin_yayasan_can_delete_member(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $member = $this->makeMember();

        $this->delete('/admin/members/' . $member->id)->assertRedirect();

        $this->assertSoftDeleted('members', ['id' => $member->id]);
    }

    public function test_invalid_group_rejected(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->post('/admin/members', [
            'name' => 'Siapa',
            'group' => 'jabatan_lain',
            'position' => 'Bendahara',
            'status' => 'active',
        ])->assertSessionHasErrors('group');
    }

    protected function makeMember(): Member
    {
        return Member::create([
            'tenant_id' => $this->tenantId(),
            'name' => 'Budi Lama',
            'group' => 'pengurus_inti',
            'position' => 'Ketua',
            'status' => 'active',
        ]);
    }
}