<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantResolveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        app(\App\Support\TenantContext::class)->clear();
    }

    public function test_resolves_tenant_by_subdomain(): void
    {
        $response = $this->get('http://kerkomit.test/');

        $response->assertStatus(200);
        $this->assertTrue(app(TenantContext::class)->has());
        $this->assertSame('Yayasan Kerkomit', app(TenantContext::class)->get()->name);
    }

    public function test_resolves_tenant_by_custom_domain(): void
    {
        Tenant::create([
            'name' => 'Yayasan Contoh',
            'subdomain' => 'contoh',
            'custom_domain' => 'contoh.or.id',
            'status' => 'active',
        ]);

        $response = $this->get('http://contoh.or.id/');

        $response->assertStatus(200);
        $this->assertSame('Yayasan Contoh', app(TenantContext::class)->get()->name);
    }

    public function test_unknown_domain_returns_404(): void
    {
        $response = $this->get('http://tidakada.test/');

        $response->assertStatus(404);
        $this->assertFalse(app(TenantContext::class)->has());
    }

    public function test_inactive_tenant_not_resolvable(): void
    {
        Tenant::create([
            'name' => 'Yayasan Nonaktif',
            'subdomain' => 'nonaktif',
            'status' => 'suspended',
        ]);

        $response = $this->get('http://nonaktif.test/');

        $response->assertStatus(404);
    }
}
