<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramPublicTest extends TestCase
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

    public function test_program_list_renders_only_published(): void
    {
        $program = Program::firstOrFail();

        $response = $this->get('http://kerkomit.test/programs');

        $response->assertStatus(200);
        $response->assertSee($program->title);
    }

    public function test_program_detail_renders_and_increments_views(): void
    {
        $program = Program::firstOrFail();
        $before = $program->views_count;

        $response = $this->get("http://kerkomit.test/program/{$program->slug}");

        $response->assertStatus(200);
        $response->assertSee($program->title);
        $response->assertSee('application/ld+json', false);

        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'views_count' => $before + 1,
        ]);
    }

    public function test_unpublished_program_not_shown(): void
    {
        $draft = Program::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Program Draf',
            'slug' => 'program-draf',
            'status' => 'ongoing',
            'published_at' => null,
        ]);

        $response = $this->get('http://kerkomit.test/program/program-draf');
        $response->assertStatus(404);

        $response = $this->get('http://kerkomit.test/programs');
        $response->assertDontSee('Program Draf');
        $this->assertNotNull($draft);
    }
}
