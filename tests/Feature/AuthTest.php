<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_view_their_profile_and_logout(): void
    {
        User::factory()->create([
            'username' => 'pemilik',
            'password' => 'secret-password',
        ]);

        $login = $this->postJson('/api/auth/login', [
            'username' => 'pemilik',
            'password' => 'secret-password',
        ]);

        $login->assertOk()
            ->assertJsonPath('user.username', 'pemilik')
            ->assertJsonPath('user.roles.0', 'KASIR')
            ->assertJsonMissingPath('user.password');

        $token = $login->json('token');
        $this->assertIsString($token);

        $this->withToken($token)
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('user.username', 'pemilik');

        $this->withToken($token)
            ->postJson('/api/auth/logout')
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', $token),
        ]);

        Auth::forgetGuards();
        $this->withToken($token)
            ->getJson('/api/auth/me')
            ->assertUnauthorized();
    }

    public function test_gates_allow_only_the_configured_roles(): void
    {
        $owner = User::factory()->create([
            'username' => 'pemilik',
        ]);
        $owner->syncRoles(['PEMILIK']);

        $cashier = User::factory()->create([
            'username' => 'kasir',
        ]);

        $this->actingAs($owner, 'sanctum')
            ->getJson('/api/owner/check')
            ->assertOk();

        $this->actingAs($cashier, 'sanctum')
            ->getJson('/api/owner/check')
            ->assertForbidden();

        $this->actingAs($cashier, 'sanctum')
            ->getJson('/api/staff/check')
            ->assertOk();
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'username' => 'kasir',
            'password' => 'correct-password',
        ]);

        $this->postJson('/api/auth/login', [
            'username' => 'kasir',
            'password' => 'incorrect-password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('username');
    }

    public function test_owner_can_manage_cashier_accounts_and_disable_access(): void
    {
        $owner = User::factory()->create(['username' => 'pemilik']);
        $owner->syncRoles(['PEMILIK']);
        $cashier = User::factory()->create([
            'username' => 'kasir-awal',
            'email' => 'kasir-awal@example.com',
        ]);
        $token = $cashier->createToken('test-token')->plainTextToken;

        $this->actingAs($owner, 'sanctum')
            ->postJson('/api/owner/cashiers', [
                'name' => 'Kasir Baru',
                'username' => 'kasir-baru',
                'email' => 'kasir-baru@example.com',
                'password' => 'strong-password',
                'password_confirmation' => 'strong-password',
            ])
            ->assertCreated()
            ->assertJsonPath('data.username', 'kasir-baru')
            ->assertJsonPath('data.is_active', true);

        $this->actingAs($owner, 'sanctum')
            ->getJson('/api/owner/cashiers')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->actingAs($owner, 'sanctum')
            ->patchJson('/api/owner/cashiers/'.$cashier->id, [
                'name' => 'Kasir Diperbarui',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Kasir Diperbarui');

        $this->actingAs($owner, 'sanctum')
            ->deleteJson('/api/owner/cashiers/'.$cashier->id)
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('users', [
            'id' => $cashier->id,
            'is_active' => false,
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', $token),
        ]);

        $this->postJson('/api/auth/login', [
            'username' => 'kasir-awal',
            'password' => 'password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('username');
    }

    public function test_only_owners_can_manage_cashier_accounts(): void
    {
        $cashier = User::factory()->create(['username' => 'kasir']);

        $this->actingAs($cashier, 'sanctum')
            ->getJson('/api/owner/cashiers')
            ->assertForbidden();
    }

    public function test_database_seeder_creates_owner_and_cashier_accounts(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', [
            'username' => 'pemilik',
        ]);
        $this->assertDatabaseHas('users', [
            'username' => 'kasir',
        ]);
        $this->assertTrue(User::where('username', 'pemilik')->firstOrFail()->hasRole('PEMILIK'));
        $this->assertTrue(User::where('username', 'kasir')->firstOrFail()->hasRole('KASIR'));
    }

    public function test_public_registration_creates_an_owner_account(): void
    {
        $this->post('/register', [
            'name' => 'Pemilik Baru',
            'username' => 'pemilik-baru',
            'email' => 'baru@example.com',
            'password' => 'strong-password',
            'password_confirmation' => 'strong-password',
        ])->assertRedirect(route('login'));

        $user = User::where('username', 'pemilik-baru')->firstOrFail();

        $this->assertSame('baru@example.com', $user->email);
        $this->assertTrue($user->hasRole('PEMILIK'));
        $this->assertTrue(Hash::check('strong-password', $user->password));
    }

    public function test_password_reset_link_is_sent_to_the_account_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'owner@example.com']);

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_owner_can_reset_password_with_a_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-strong-password',
            'password_confirmation' => 'new-strong-password',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('new-strong-password', $user->fresh()->password));
    }

    public function test_registration_and_password_reset_forms_render(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Daftar Pemilik');

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Lupa kata sandi?');

        $this->get(route('password.reset', [
            'token' => 'sample-token',
            'email' => 'owner@example.com',
        ]))
            ->assertOk()
            ->assertSee('Buat kata sandi baru');
    }
}
