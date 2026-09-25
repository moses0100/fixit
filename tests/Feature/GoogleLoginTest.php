<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogle(string $id, string $email, string $name): void
    {
        $abstract = Mockery::mock(SocialUser::class);
        $abstract->shouldReceive('getId')->andReturn($id);
        $abstract->shouldReceive('getEmail')->andReturn($email);
        $abstract->shouldReceive('getName')->andReturn($name);
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($abstract);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_redirect_goes_to_google(): void
    {
        $res = $this->get('/auth/google');
        $res->assertRedirect();
        $this->assertStringContainsString('accounts.google.com', (string) $res->headers->get('Location'));
    }

    public function test_callback_creates_and_logs_in_new_user(): void
    {
        $this->fakeGoogle('g111', 'gnew@example.com', 'G New');
        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'gnew@example.com', 'google_id' => 'g111']);
    }

    public function test_callback_links_existing_email_account(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);
        $this->fakeGoogle('g222', 'old@example.com', 'Old Name');
        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user->fresh());
        $this->assertSame('g222', $user->fresh()->google_id);
    }

    public function test_callback_rejects_suspended_account(): void
    {
        User::factory()->create(['email' => 'ban@example.com', 'is_active' => false]);
        $this->fakeGoogle('g333', 'ban@example.com', 'Banned');
        $this->get('/auth/google/callback')->assertForbidden();
        $this->assertGuest();
    }
}
