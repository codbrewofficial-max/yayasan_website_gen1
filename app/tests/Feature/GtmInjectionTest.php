<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\CampaignLink;
use App\Models\Donation;
use App\Models\GtmConfig;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GtmInjectionTest extends TestCase
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

    protected function enableGtm(string $gtmId = 'GTM-ABC123', string $ga4 = 'G-999999999'): void
    {
        GtmConfig::create([
            'tenant_id' => $this->tenant->id,
            'gtm_id' => $gtmId,
            'ga4_measurement_id' => $ga4,
            'status' => GtmConfig::STATUS_ACTIVE,
        ]);
    }

    public function test_active_gtm_config_injects_container_snippet(): void
    {
        $this->enableGtm();

        $this->get('http://kerkomit.test/')
            ->assertStatus(200)
            ->assertSee('https://www.googletagmanager.com/gtm.js?id=', false)
            ->assertSee('GTM-ABC123')
            ->assertSee('gtm.start', false);
    }

    public function test_active_gtm_takes_precedence_over_settings_ga4(): void
    {
        app(\App\Services\SettingService::class)->setMany([
            'ga_measurement_id' => 'G-OLD111111',
        ]);

        $this->enableGtm();

        $this->get('http://kerkomit.test/')
            ->assertStatus(200)
            ->assertDontSee('id=G-OLD111111');
    }

    public function test_inactive_gtm_falls_back_to_settings_ga4(): void
    {
        app(\App\Services\SettingService::class)->setMany([
            'ga_measurement_id' => 'G-OLD111111',
        ]);

        $this->get('http://kerkomit.test/')
            ->assertStatus(200)
            ->assertSee('id=G-OLD111111');
    }

    public function test_article_page_pushes_article_view_event(): void
    {
        $article = \App\Models\Article::where('slug', 'serah-terima-beasiswa-2026')->firstOrFail();

        $this->get('http://kerkomit.test/article/' . $article->slug)
            ->assertSee('article_view');
    }

    public function test_program_page_pushes_program_view_event(): void
    {
        $program = \App\Models\Program::where('slug', 'beasiswa-anak-yatim')->firstOrFail();

        $this->get('http://kerkomit.test/program/' . $program->slug)
            ->assertSee('program_view');
    }

    public function test_donation_page_pushes_donation_started_event(): void
    {
        $this->get('http://kerkomit.test/donasi/beasiswa-batch-2026')
            ->assertSee('donation_started');
    }

    public function test_paid_donation_status_pushes_donation_completed(): void
    {
        $campaign = Campaign::where('slug', 'beasiswa-batch-2026')->firstOrFail();
        $donation = Donation::create([
            'tenant_id' => $this->tenant->id,
            'campaign_id' => $campaign->id,
            'order_id' => 'TKERKOMIT-' . fake()->uuid(),
            'donor_name' => 'Siti',
            'donor_email' => 'siti-gtm@example.com',
            'donor_phone' => '081298765432',
            'amount' => 100000,
            'payment_status' => Donation::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $this->get('http://kerkomit.test/donasi/' . $campaign->slug . '/status/' . $donation->order_id)
            ->assertSee('donation_completed');
    }

    public function test_contact_success_pushes_lead_submitted(): void
    {
        Mail::fake();

        $this->post('http://kerkomit.test/kontak', [
            'name' => 'Andi',
            'email' => 'andi@example.com',
            'message' => 'Halo',
            'lead_type' => 'email',
        ])->assertSessionHas('success');

        $this->get('http://kerkomit.test/kontak')->assertSee('lead_submitted');
    }

    public function test_shortlink_shows_intermediate_page_when_gtm_active(): void
    {
        $this->enableGtm();

        $campaign = Campaign::where('slug', 'beasiswa-batch-2026')->firstOrFail();
        $link = CampaignLink::create([
            'tenant_id' => $this->tenant->id,
            'campaign_id' => $campaign->id,
            'label' => 'Bio',
            'utm_source' => 'instagram',
            'utm_medium' => 'social',
            'short_code' => 'GTM123',
            'target_url' => 'http://kerkomit.test/campaign/beasiswa-batch-2026',
            'created_by' => $campaign->author_id,
        ]);

        $response = $this->get('/go/GTM123');

        $response->assertStatus(200)->assertSee('campaign_link_click');

        $this->assertDatabaseHas('link_clicks', ['campaign_link_id' => $link->id]);
    }

    public function test_shortlink_keeps_302_when_gtm_inactive(): void
    {
        $campaign = Campaign::where('slug', 'beasiswa-batch-2026')->firstOrFail();
        $link = CampaignLink::create([
            'tenant_id' => $this->tenant->id,
            'campaign_id' => $campaign->id,
            'label' => 'Bio',
            'utm_source' => 'instagram',
            'utm_medium' => 'social',
            'short_code' => 'NOGTM1',
            'target_url' => 'http://kerkomit.test/campaign/beasiswa-batch-2026',
            'created_by' => $campaign->author_id,
        ]);

        $this->get('/go/NOGTM1')->assertRedirect($link->target_url);
    }
}
