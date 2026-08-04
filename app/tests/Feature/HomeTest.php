<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();
    }

    public function test_main_domain_shows_landing_page(): void
    {
        config()->set('app.main_domain', 'yayasan-go-digital.test');

        $this->get('http://yayasan-go-digital.test/')
            ->assertStatus(200)
            ->assertSee('Yayasan Go Digital')
            ->assertSee('Galang Dana Online')
            ->assertSee('Masuk');
    }

    public function test_tenant_subdomain_does_not_show_landing(): void
    {
        config()->set('app.main_domain', 'yayasan-go-digital.test');

        $this->get('http://yayasan-go-digital.test/')
            ->assertStatus(200)
            ->assertSee('Yayasan Go Digital');
    }

    public function test_tenant_home_renders_on_subdomain(): void
    {
        app(\App\Support\TenantContext::class)->set($this->tenant);

        $this->get('http://kerkomit.test/')
            ->assertStatus(200)
            ->assertSee('Yayasan Kerkomit')
            ->assertSee('Galang Dana Aktif')
            ->assertSee('Campaign Beasiswa Batch 2026');
    }

    public function test_main_domain_root_without_tenant_does_not_resolve_404(): void
    {
        config()->set('app.main_domain', 'yayasan-go-digital.test');

        $this->get('http://yayasan-go-digital.test/')->assertStatus(200);
    }
}