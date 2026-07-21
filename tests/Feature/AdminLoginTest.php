<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_renders(): void
    {
        $response = $this->get(route('admin.login'));
        $response->assertStatus(200);
        $response->assertSee('Masuk');
        $response->assertSee('Email');
        $response->assertSee('Password');
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
            'is_admin' => true,
            'role' => 'admin',
        ]);

        Livewire::test('admin.login')
            ->set('email', 'admin@test.com')
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_admin_cannot_login_with_invalid_credentials(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
            'is_admin' => true,
            'role' => 'admin',
        ]);

        Livewire::test('admin.login')
            ->set('email', 'admin@test.com')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_non_admin_redirected_from_admin_dashboard(): void
    {
        $user = User::create([
            'name' => 'User',
            'email' => 'user@test.com',
            'password' => 'password',
            'is_admin' => false,
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_guest_redirected_from_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
    }
}
