<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberPublicTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();
        app(\App\Support\TenantContext::class)->set($this->tenant);
    }

    public function test_members_page_renders_groups_sections(): void
    {
        $response = $this->get('http://kerkomit.test/pengurus');

        $response->assertStatus(200);
        $response->assertSee('Struktur Pengurus');
        $response->assertSee('Pembina');
        $response->assertSee('Pengawas');
        $response->assertSee('Pengurus Inti');
        $response->assertSee('Anggota');
        $response->assertSee('Dewi Lestari');
        $response->assertSee('Ketua');
    }

    public function test_inactive_member_not_shown(): void
    {
        Member::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Mantan Pengurus',
            'group' => Member::GROUP_ANGGOTA,
            'position' => 'Eks',
            'status' => Member::STATUS_INACTIVE,
        ]);

        $emptyGroup = collect(Member::GROUPS)->mapWithKeys(fn ($g) => [$g => collect()]);
        $active = Member::where('status', Member::STATUS_ACTIVE)->get();
        $groups = $emptyGroup
            ->map(fn ($c, $g) => $active->where('group', $g)->values())
            ->filter->isNotEmpty();

        $response = $this->get('http://kerkomit.test/pengurus');

        $response->assertStatus(200);
        $response->assertDontSee('Mantan Pengurus');
        $this->assertTrue($groups->isNotEmpty());
    }
}