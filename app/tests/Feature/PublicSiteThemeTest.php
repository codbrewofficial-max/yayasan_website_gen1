<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Services\SettingService;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PublicSiteThemeTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Cache::flush();
        $this->tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();
    }

    protected function saveSettings(array $values): void
    {
        app(TenantContext::class)->set($this->tenant);
        app(SettingService::class)->setMany($values);
    }

    public function test_home_uses_custom_site_name_from_settings(): void
    {
        $this->saveSettings(['site_name' => 'Yayasan Kerkomit Baru', 'site_tagline' => 'Peduli Bersama']);

        $this->get('http://kerkomit.test/')
            ->assertStatus(200)
            ->assertSee('Yayasan Kerkomit Baru')
            ->assertSee('Peduli Bersama');
    }

    public function test_home_falls_back_to_tenant_name_when_site_name_unset(): void
    {
        $this->get('http://kerkomit.test/')
            ->assertStatus(200)
            ->assertSee('Yayasan Kerkomit');
    }

    public function test_theme_color_is_injected_into_layout(): void
    {
        $this->saveSettings(['theme_color' => '#b91c1c']);

        $this->get('http://kerkomit.test/')
            ->assertStatus(200)
            ->assertSee('--color-primary: #b91c1c');
    }

    public function test_ga_script_rendered_when_id_set(): void
    {
        $this->saveSettings(['ga_measurement_id' => 'G-ABC123']);

        $this->get('http://kerkomit.test/')
            ->assertSee('https://www.googletagmanager.com/gtag/js?id=G-ABC123')
            ->assertSee("gtag('config', 'G-ABC123');", false);
    }

    public function test_header_shows_nav_and_donation_cta(): void
    {
        $this->get('http://kerkomit.test/')
            ->assertSee('Galang Dana')
            ->assertSee('Galeri')
            ->assertSee('Pengurus')
            ->assertSee('Donasi Sekarang')
            ->assertViewHas('campaigns');
    }

    public function test_footer_shows_contact_and_social_from_settings(): void
    {
        $this->saveSettings([
            'contact_email' => 'info@kerkomit.test',
            'whatsapp_number' => '+6281234567890',
            'social_instagram' => 'https://instagram.com/kerkomit',
        ]);

        $this->get('http://kerkomit.test/')
            ->assertSee('info@kerkomit.test')
            ->assertSee('https://wa.me/6281234567890')
            ->assertSee('https://instagram.com/kerkomit');
    }

    public function test_sitemap_renders_tenant_urls(): void
    {
        $response = $this->get('http://kerkomit.test/sitemap.xml');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertStringContainsString('<loc>http://kerkomit.test</loc>', $content);
        $this->assertStringContainsString('beasiswa-anak-yatim', $content);
        $this->assertStringContainsString('campaign', $content);
        $this->assertStringContainsString('serah-terima-beasiswa-2026', $content);
        $this->assertStringContainsString('tentang', $content);
    }

    public function test_sitemap_on_main_domain_returns_404(): void
    {
        $this->get('http://yayasan-go-digital.test/sitemap.xml')->assertStatus(404);
    }

    public function test_campaign_detail_uses_theme_color_cta(): void
    {
        $this->saveSettings(['theme_color' => '#0d9488']);

        $this->get('http://kerkomit.test/campaign/beasiswa-batch-2026')
            ->assertStatus(200)
            ->assertSee('bg-primary')
            ->assertSee('Donasi Sekarang');
    }

    public function test_donation_page_shows_notice_and_min_amount_from_settings(): void
    {
        $this->saveSettings([
            'donation_notice' => 'Terima kasih sudah berdonasi.',
            'donation_min_amount' => 50000,
        ]);

        $this->get('http://kerkomit.test/donasi/beasiswa-batch-2026')
            ->assertStatus(200)
            ->assertSee('Terima kasih sudah berdonasi.')
            ->assertSee('min="50000"', false);
    }

    public function test_all_public_pages_render_for_tenant(): void
    {
        foreach (['/', '/programs', '/campaigns', '/articles', '/albums', '/pengurus', '/kontak', '/page/tentang'] as $path) {
            $this->get('http://kerkomit.test' . $path)
                ->assertStatus(200);
        }
    }
}