<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\CampaignLink;
use App\Models\LinkClick;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignLinkTest extends TestCase
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

    protected function makeLink(array $overrides = []): CampaignLink
    {
        $campaign = Campaign::where('slug', 'beasiswa-batch-2026')->firstOrFail();

        return CampaignLink::create([
            'tenant_id' => $this->tenant->id,
            'campaign_id' => $campaign->id,
            'label' => 'Bio Instagram',
            'utm_source' => 'instagram',
            'utm_medium' => 'social',
            'utm_campaign' => $campaign->slug,
            'short_code' => 'ABC123',
            'target_url' => 'http://kerkomit.test/campaign/' . $campaign->slug . '?utm_source=instagram',
            'created_by' => $campaign->author_id,
            ...$overrides,
        ]);
    }

    public function test_short_link_redirects_and_logs_click(): void
    {
        $link = $this->makeLink();

        $response = $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (iPhone) Safari'])
            ->get('/go/' . $link->short_code);

        $response->assertStatus(302);
        $response->assertRedirect($link->target_url);

        $this->assertDatabaseHas('link_clicks', [
            'campaign_link_id' => $link->id,
            'device_type' => LinkClick::DEVICE_MOBILE,
        ]);

        $this->assertDatabaseHas('campaign_links', [
            'id' => $link->id,
            'clicks_count' => 1,
        ]);

        $this->assertNotNull($link->fresh()->last_clicked_at);
    }

    public function test_unknown_short_code_returns_404(): void
    {
        $this->get('/go/ZZZZZZ')->assertStatus(404);
    }

    public function test_utm_captured_to_session_from_query(): void
    {
        $this->get('http://kerkomit.test/campaign/beasiswa-batch-2026?utm_source=facebook&utm_medium=cpc');

        $this->assertSame([
            'utm_source' => 'facebook',
            'utm_medium' => 'cpc',
        ], session(\App\Http\Middleware\CaptureUtm::SESSION_KEY));
    }

    public function test_donation_attributes_utm_from_session(): void
    {
        $this->get('http://kerkomit.test/campaign/beasiswa-batch-2026?utm_source=whatsapp&utm_medium=social');

        $response = $this->post('http://kerkomit.test/donasi/beasiswa-batch-2026', [
            'donor_name' => 'Budi',
            'donor_email' => 'budi-utm@example.com',
            'donor_phone' => '081234567890',
            'amount' => 100000,
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('donations', [
            'donor_email' => 'budi-utm@example.com',
            'utm_source' => 'whatsapp',
            'utm_medium' => 'social',
        ]);
    }

    public function test_donation_attributes_campaign_link_from_short_link_session(): void
    {
        $link = $this->makeLink();

        // Klik short link → session campaign_link_id ter-set.
        $this->get('/go/' . $link->short_code);

        $response = $this->post('http://kerkomit.test/donasi/beasiswa-batch-2026', [
            'donor_name' => 'Budi',
            'donor_email' => 'budi-link@example.com',
            'donor_phone' => '081234567890',
            'amount' => 100000,
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('donations', [
            'donor_email' => 'budi-link@example.com',
            'campaign_link_id' => $link->id,
        ]);
    }

    public function test_donation_ignores_cross_campaign_link(): void
    {
        $other = Campaign::where('slug', 'dana-darurat-beasiswa')->firstOrFail();
        $link = $this->makeLink([
            'campaign_id' => $other->id,
            'short_code' => 'DEF456',
        ]);

        $this->session(['campaign_link_id' => $link->id]);

        $response = $this->post('http://kerkomit.test/donasi/beasiswa-batch-2026', [
            'donor_name' => 'Budi',
            'donor_email' => 'budi-xlink@example.com',
            'donor_phone' => '081234567890',
            'amount' => 100000,
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('donations', [
            'donor_email' => 'budi-xlink@example.com',
            'campaign_link_id' => null,
        ]);
    }
}