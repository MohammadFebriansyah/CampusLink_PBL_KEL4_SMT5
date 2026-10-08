<?php

namespace Tests\Feature\Auth;

use App\Models\Lecturer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterLecturerTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register/dosen');

        $response->assertStatus(200);
        $response->assertSee('Daftar Akun Dosen');
    }

    public function test_new_lecturers_can_register(): void
    {
        $response = $this->post('/register/dosen', [
            'name' => 'Dian Hanifudin Subhi, S.Kom., M.Kom.',
            'nidn_nip' => '0019088501',
            'email' => 'dian.hanifudin@polinema.ac.id',
            'homebase' => 'Politeknik Negeri Malang - Jurusan Teknologi Informasi',
            'expertise' => 'Web Engineering, Distributed Systems',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');

        $user = User::where('email', 'dian.hanifudin@polinema.ac.id')->first();
        $this->assertNotNull($user);
        $this->assertEquals('lecturer', $user->role);
        $this->assertTrue(Hash::check('secret123', $user->password));

        $lecturer = Lecturer::where('user_id', $user->id)->first();
        $this->assertNotNull($lecturer);
        $this->assertEquals('0019088501', $lecturer->nidn);
        $this->assertEquals('Politeknik Negeri Malang - Jurusan Teknologi Informasi', $lecturer->institution);
        $this->assertTrue($lecturer->is_pddikti_verified);
    }

    public function test_registration_fails_if_email_is_not_ac_id(): void
    {
        $response = $this->post('/register/dosen', [
            'name' => 'Dian Hanifudin Subhi',
            'nidn_nip' => '0019088501',
            'email' => 'dian.hanifudin@gmail.com',
            'homebase' => 'Politeknik Negeri Malang',
            'expertise' => 'Web Engineering',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_fails_if_password_confirmation_does_not_match(): void
    {
        $response = $this->post('/register/dosen', [
            'name' => 'Dian Hanifudin Subhi',
            'nidn_nip' => '0019088501',
            'email' => 'dian.hanifudin@polinema.ac.id',
            'homebase' => 'Politeknik Negeri Malang',
            'expertise' => 'Web Engineering',
            'password' => 'secret123',
            'password_confirmation' => 'different123',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_registration_fails_if_email_or_nidn_is_not_unique(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'dian.hanifudin@polinema.ac.id',
        ]);

        Lecturer::create([
            'user_id' => $existingUser->id,
            'nidn' => '0019088501',
            'institution' => 'Polinema',
        ]);

        $response = $this->post('/register/dosen', [
            'name' => 'Dosen Lain',
            'nidn_nip' => '0019088501',
            'email' => 'dian.hanifudin@polinema.ac.id',
            'homebase' => 'Politeknik Negeri Malang',
            'expertise' => 'Web Engineering',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['email', 'nidn_nip']);
        $this->assertGuest();
    }
}
