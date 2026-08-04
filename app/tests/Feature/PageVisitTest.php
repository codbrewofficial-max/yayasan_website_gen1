<?php

namespace Tests\Feature;

use App\Models\PageVisit;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageVisitTest extends TestCase
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

    public function test_page_visit_recorded_and_session_set(): void
    {
        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (iPhone)'])
            ->get('http://kerkomit.test/campaigns');

        $this->assertDatabaseHas('page_visits', [
            'page_url' => 'campaigns',
            'device_type' => PageVisit::DEVICE_MOBILE,
        ]);

        $this->assertNotNull(session('page_visit_id'));
    }

    public function test_page_visit_deduplicated_per_session_day(): void
    {
        $this->get('http://kerkomit.test/campaigns');
        $this->get('http://kerkomit.test/campaigns');

        $this->assertDatabaseCount('page_visits', 1);
    }

    public function test_redirect_and_api_are_not_recorded(): void
    {
        $this->post('http://kerkomit.test/donasi/beasiswa-batch-2026', [
            'donor_name' => 'Budi',
            'donor_email' => 'budi@example.com',
            'donor_phone' => '081234567890',
            'amount' => 100000,
        ]);

        $this->assertDatabaseCount('page_visits', 0);
    }

    public function test_donation_attributes_page_visit_id_from_session(): void
    {
        $this->get('http://kerkomit.test/campaigns');
        $visitId = session('page_visit_id');

        $this->post('http://kerkomit.test/donasi/beasiswa-batch-2026', [
            'donor_name' => 'Budi',
            'donor_email' => 'budi-pv@example.com',
            'donor_phone' => '081234567890',
            'amount' => 100000,
        ]);

        $this->assertDatabaseHas('donations', [
            'donor_email' => 'budi-pv@example.com',
            'page_visit_id' => $visitId,
        ]);
    }
}