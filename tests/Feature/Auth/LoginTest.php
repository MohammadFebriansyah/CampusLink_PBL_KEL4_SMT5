<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke CampusLink');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'mahasiswa@polinema.ac.id',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => 'mahasiswa@polinema.ac.id',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'mahasiswa@polinema.ac.id',
            'password' => 'password123',
        ]);

        $this->post('/login', [
            'email' => 'mahasiswa@polinema.ac.id',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
