<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Program;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignPublicTest extends TestCase
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

    public function test_campaign_list_renders_only_active(): void
    {
        $response = $this->get('http://kerkomit.test/campaigns');

        $response->assertStatus(200);
        $response->assertSee('Campaign Beasiswa Batch 2026');
        $response->assertSee('Dana Darurat Beasiswa');
        $response->assertDontSee('Bantuan Gempa Cianjur');
    }

    public function test_campaign_detail_renders_stats_and_schema(): void
    {
        $campaign = Campaign::where('slug', 'beasiswa-batch-2026')->firstOrFail();
        $before = $campaign->views_count;

        $response = $this->get('http://kerkomit.test/campaign/beasiswa-batch-2026');

        $response->assertStatus(200);
        $response->assertSee('Campaign Beasiswa Batch 2026');
        $response->assertSee('66%');
        $response->assertSee('application/ld+json', false);

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'views_count' => $before + 1,
        ]);
    }

    public function test_draft_campaign_not_public(): void
    {
        $program = Program::firstOrFail();
        Campaign::create([
            'tenant_id' => $this->tenant->id,
            'program_id' => $program->id,
            'title' => 'Campaign Draf',
            'slug' => 'campaign-draf',
            'status' => Campaign::STATUS_DRAFT,
        ]);

        $response = $this->get('http://kerkomit.test/campaign/campaign-draf');
        $response->assertStatus(404);
    }

    public function test_program_detail_shows_related_campaigns(): void
    {
        $response = $this->get('http://kerkomit.test/program/beasiswa-anak-yatim');

        $response->assertStatus(200);
        $response->assertSee('Campaign Terkait');
        $response->assertSee('Campaign Beasiswa Batch 2026');
    }
}
