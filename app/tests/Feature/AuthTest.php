<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_without_2fa_redirects_to_dashboard(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'staff@test.test',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'staff@test.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_2fa_requires_challenge(): void
    {
        $secret = (new Google2FA())->generateSecretKey();
        $user = User::create([
            'name' => 'Super',
            'email' => 'super2fa@test.test',
            'password' => Hash::make('password'),
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => json_encode(['recovery-code-1']),
        ]);

        $response = $this->post('/login', [
            'email' => 'super2fa@test.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();

        $code = (new Google2FA())->getCurrentOtp($secret);
        $response = $this->post('/two-factor/challenge', ['code' => $code]);
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_recovery_code_can_verify(): void
    {
        $secret = (new Google2FA())->generateSecretKey();
        $user = User::create([
            'name' => 'Super',
            'email' => 'recovery@test.test',
            'password' => Hash::make('password'),
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => json_encode(['recovery-code-1']),
        ]);

        $this->post('/login', [
            'email' => 'recovery@test.test',
            'password' => 'password',
        ]);

        $response = $this->post('/two-factor/challenge', ['code' => 'recovery-code-1']);
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_recovery_codes' => json_encode([]),
        ]);
    }
}
