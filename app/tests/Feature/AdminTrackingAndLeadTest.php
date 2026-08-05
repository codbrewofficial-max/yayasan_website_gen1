<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\CampaignLink;
use App\Models\Donation;
use App\Models\Lead;
use App\Models\Program;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTrackingAndLeadTest extends TestCase
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

    protected function campaign(): Campaign
    {
        $tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();
        $program = Program::create([
            'tenant_id' => $tenant->id,
            'title' => 'Program',
            'slug' => 'program',
            'status' => 'ongoing',
        ]);

        return Campaign::create([
            'tenant_id' => $tenant->id,
            'program_id' => $program->id,
            'title' => 'Bantuan Korban Banjir',
            'slug' => 'bantuan-korban-banjir',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_create_tracking_link_with_short_code_and_utm(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $campaign = $this->campaign();

        $this->post('/admin/campaign-links', [
            'campaign_id' => $campaign->id,
            'label' => 'FB Banjir',
            'utm_source' => 'facebook',
            'utm_medium' => 'social',
            'utm_campaign' => 'banjir-2026',
        ])->assertRedirect();

        $link = CampaignLink::query()->where('label', 'FB Banjir')->firstOrFail();
        $this->assertSame(6, strlen($link->short_code));
        $this->assertStringContainsString('utm_source=facebook', $link->target_url);
        $this->assertStringContainsString('bantuan-korban-banjir', $link->target_url);
    }

    public function test_short_code_is_unique(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $campaign = $this->campaign();

        $payload = [
            'campaign_id' => $campaign->id,
            'label' => 'Link A',
            'utm_source' => 'fb',
            'utm_medium' => 'social',
        ];

        $this->post('/admin/campaign-links', $payload);
        $this->post('/admin/campaign-links', $payload);

        $this->assertSame(2, CampaignLink::query()->where('label', 'Link A')->count());
        $codes = CampaignLink::query()->where('label', 'Link A')->pluck('short_code');
        $this->assertCount(2, $codes->unique());
    }

    public function test_index_shows_conversion_summary(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $campaign = $this->campaign();
        $link = CampaignLink::create([
            'tenant_id' => $campaign->tenant_id,
            'campaign_id' => $campaign->id,
            'label' => 'Link A',
            'utm_source' => 'fb',
            'utm_medium' => 'social',
            'short_code' => 'abc123',
            'target_url' => route('public.campaign', $campaign->slug),
            'clicks_count' => 12,
        ]);
        Donation::create([
            'tenant_id' => $campaign->tenant_id,
            'campaign_id' => $campaign->id,
            'order_id' => 'T-1',
            'donor_name' => 'A',
            'donor_email' => 'a@a.test',
            'donor_phone' => '1',
            'amount' => 50000,
            'payment_status' => 'paid',
            'campaign_link_id' => $link->id,
        ]);

        $this->get('/admin/campaign-links')
            ->assertStatus(200)
            ->assertSee('abc123')
            ->assertSee('50.000');
    }

    public function test_admin_can_update_tracking_link(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $campaign = $this->campaign();
        $link = CampaignLink::create([
            'tenant_id' => $campaign->tenant_id,
            'campaign_id' => $campaign->id,
            'label' => 'Lama',
            'utm_source' => 'fb',
            'utm_medium' => 'social',
            'short_code' => 'zzz111',
            'target_url' => route('public.campaign', $campaign->slug),
        ]);

        $this->put('/admin/campaign-links/' . $link->id, [
            'campaign_id' => $campaign->id,
            'label' => 'Baru',
            'utm_source' => 'instagram',
            'utm_medium' => 'social',
        ])->assertRedirect();

        $this->assertDatabaseHas('campaign_links', [
            'id' => $link->id,
            'label' => 'Baru',
            'short_code' => 'zzz111',
        ]);
    }

    public function test_admin_can_list_and_show_leads(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $lead = Lead::create([
            'tenant_id' => Tenant::where('subdomain', 'kerkomit')->firstOrFail()->id,
            'name' => 'Siti',
            'email' => 'siti@test.test',
            'phone' => '0811',
            'message' => 'Pertanyaan donasi.',
            'lead_type' => 'email',
        ]);

        $this->get('/admin/leads')->assertStatus(200)->assertSee('Siti');
        $this->get('/admin/leads/' . $lead->id)->assertStatus(200)->assertSee('Pertanyaan donasi.');
    }

    public function test_admin_can_update_lead_status(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $lead = Lead::create([
            'tenant_id' => Tenant::where('subdomain', 'kerkomit')->firstOrFail()->id,
            'name' => 'Siti',
            'email' => 'siti@test.test',
            'message' => 'Halo',
            'lead_type' => 'email',
            'status' => 'new',
        ]);

        $this->put('/admin/leads/' . $lead->id . '/status', ['status' => 'processing'])->assertRedirect();

        $this->assertDatabaseHas('leads', ['id' => $lead->id, 'status' => 'processing']);
    }

    public function test_admin_can_delete_lead(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $lead = Lead::create([
            'tenant_id' => Tenant::where('subdomain', 'kerkomit')->firstOrFail()->id,
            'name' => 'Siti',
            'email' => 'siti@test.test',
            'message' => 'Halo',
            'lead_type' => 'email',
        ]);

        $this->delete('/admin/leads/' . $lead->id)->assertRedirect();

        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
    }
}