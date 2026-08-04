<?php

namespace Tests\Feature;

use App\Mail\LeadContactMail;
use App\Models\Lead;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LeadTest extends TestCase
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

    public function test_contact_page_renders(): void
    {
        $this->get('http://kerkomit.test/kontak')
            ->assertStatus(200)
            ->assertSee('Hubungi Kami')
            ->assertSee('Kirim via WhatsApp');
    }

    public function test_email_lead_is_stored_and_mail_sent(): void
    {
        Mail::fake();

        $response = $this->post('http://kerkomit.test/kontak', [
            'name' => 'Andi',
            'email' => 'andi@example.com',
            'phone' => '081200000001',
            'subject' => 'Kerjasama',
            'message' => 'Halo, kami ingin kerjasama.',
            'lead_type' => Lead::TYPE_EMAIL,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leads', [
            'name' => 'Andi',
            'email' => 'andi@example.com',
            'lead_type' => Lead::TYPE_EMAIL,
            'status' => Lead::STATUS_NEW,
        ]);

        Mail::assertQueued(LeadContactMail::class, fn ($mail) => $mail->hasTo('admin@kerkomit.test'));
    }

    public function test_whatsapp_lead_is_stored_and_redirects_to_wa(): void
    {
        $response = $this->post('http://kerkomit.test/kontak', [
            'name' => 'Budi',
            'phone' => '081200000002',
            'message' => 'Mau donasi, gimana caranya?',
            'lead_type' => Lead::TYPE_WHATSAPP,
        ]);

        $response->assertStatus(302);
        $response->assertRedirectContains('https://wa.me/081234567890');

        $this->assertDatabaseHas('leads', [
            'name' => 'Budi',
            'lead_type' => Lead::TYPE_WHATSAPP,
        ]);
    }

    public function test_contact_validation(): void
    {
        $response = $this->post('http://kerkomit.test/kontak', [
            'name' => '',
            'message' => '',
            'lead_type' => 'email',
        ]);

        $response->assertSessionHasErrors(['name', 'message']);
        $this->assertDatabaseCount('leads', 0);
    }
}