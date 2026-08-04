<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCmsCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function loginAs(string $email): void
    {
        $this->actingAs(User::where('email', $email)->firstOrFail());
    }

    public function test_admin_yayasan_can_list_programs(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->get('/admin/programs')
            ->assertStatus(200)
            ->assertSee('Program');
    }

    public function test_admin_yayasan_can_create_program(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->post('/admin/programs', [
            'title' => 'Program Pendidikan Baru',
            'content' => 'Membiayai beasiswa.',
            'category' => 'pendidikan',
            'status' => 'ongoing',
            'location' => 'Bandung',
        ])->assertRedirect();

        $this->assertDatabaseHas('programs', [
            'title' => 'Program Pendidikan Baru',
            'slug' => 'program-pendidikan-baru',
            'category' => 'pendidikan',
            'status' => 'ongoing',
        ]);
    }

    public function test_slug_is_unique_within_tenant(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->post('/admin/programs', [
            'title' => 'Program Duplikat',
            'status' => 'ongoing',
        ]);
        $this->post('/admin/programs', [
            'title' => 'Program Duplikat',
            'status' => 'ongoing',
        ]);

        $this->assertDatabaseHas('programs', ['slug' => 'program-duplikat']);
        $this->assertDatabaseHas('programs', ['slug' => 'program-duplikat-2']);
    }

    public function test_admin_yayasan_can_update_program_with_image(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $program = Program::create([
            'tenant_id' => $this->tg('kerkomit'),
            'title' => 'Program Lama',
            'slug' => 'program-lama',
            'status' => 'ongoing',
        ]);

        $response = $this->put('/admin/programs/' . $program->id, [
            'title' => 'Program Diubah',
            'status' => 'completed',
            'featured_image' => $this->image(),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'title' => 'Program Diubah',
            'slug' => 'program-diubah',
            'status' => 'completed',
        ]);

        $this->assertNotNull($program->fresh()->featured_image_id);

        \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory('media');
    }

    public function test_admin_yayasan_can_create_campaign(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $program = Program::create([
            'tenant_id' => $this->tg('kerkomit'),
            'title' => 'Program Donasi',
            'slug' => 'program-donasi',
            'status' => 'ongoing',
        ]);

        $this->post('/admin/campaigns', [
            'program_id' => $program->id,
            'title' => 'Galang Dana Korban Banjir',
            'story' => 'Bantu korban banjir.',
            'target_amount' => 10000000,
            'status' => 'active',
            'donation_type' => 'one_time',
            'start_date' => '2026-08-01',
            'end_date' => '2026-12-01',
            'show_donor_list' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('campaigns', [
            'title' => 'Galang Dana Korban Banjir',
            'slug' => 'galang-dana-korban-banjir',
            'status' => 'active',
            'allow_anonymous' => false,
            'show_donor_list' => true,
        ]);
    }

    public function test_admin_yayasan_can_delete_program(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $program = Program::create([
            'tenant_id' => $this->tg('kerkomit'),
            'title' => 'Hapus Saya',
            'slug' => 'hapus-saya',
            'status' => 'ongoing',
        ]);

        $this->delete('/admin/programs/' . $program->id)->assertRedirect();

        $this->assertSoftDeleted('programs', ['id' => $program->id]);
    }

    public function test_unauthenticated_redirected_to_login(): void
    {
        $this->get('/admin/programs')->assertRedirect(route('login'));
    }

    public function test_user_without_permission_forbidden(): void
    {
        $tenantId = $this->tg('kerkomit');
        $user = User::create([
            'tenant_id' => $tenantId,
            'name' => 'Donatur',
            'email' => 'donatur@kerkomit.test',
            'password' => Hash::make('password'),
        ]);
        app()[\Spatie\Permission\PermissionRegistrar::class]->setPermissionsTeamId($tenantId);
        $user->assignRole('donatur');

        $this->actingAs($user);

        $this->get('/admin/programs')->assertStatus(403);
        $this->get('/admin/campaigns')->assertStatus(403);
    }

    protected function tg(string $subdomain): string
    {
        return \App\Models\Tenant::where('subdomain', $subdomain)->firstOrFail()->id;
    }

    protected function image(): UploadedFile
    {
        $img = imagecreatetruecolor(600, 400);
        $color = imagecolorallocate($img, 200, 100, 50);
        imagefill($img, 0, 0, $color);
        ob_start();
        imagejpeg($img, null, 90);
        $data = ob_get_clean();
        imagedestroy($img);

        $tmp = tempnam(sys_get_temp_dir(), 'img') . '.jpg';
        file_put_contents($tmp, $data);

        return new UploadedFile($tmp, 'foto.jpg', 'image/jpeg', null, true);
    }
}