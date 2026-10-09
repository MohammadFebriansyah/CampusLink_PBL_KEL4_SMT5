<?php

namespace Tests\Feature\Auth;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterStudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register/mahasiswa');

        $response->assertStatus(200);
        $response->assertSee('Daftar Akun Mahasiswa');
    }

    public function test_new_students_can_register(): void
    {
        $response = $this->post('/register/mahasiswa', [
            'name' => 'Derikho Nabima S.N',
            'email' => 'derikho@mahasiswa.polinema.ac.id',
            'nim' => '244107060050',
            'study_program' => 'D-IV SIB - Polinema',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');

        $user = User::where('email', 'derikho@mahasiswa.polinema.ac.id')->first();
        $this->assertNotNull($user);
        $this->assertEquals('student', $user->role);
        $this->assertTrue(Hash::check('secret123', $user->password));

        $student = Student::where('user_id', $user->id)->first();
        $this->assertNotNull($student);
        $this->assertEquals('244107060050', $student->nim);
        $this->assertEquals('D-IV SIB - Polinema', $student->study_program);
        $this->assertTrue($student->is_pddikti_verified);
    }

    public function test_registration_fails_if_email_is_not_ac_id(): void
    {
        $response = $this->post('/register/mahasiswa', [
            'name' => 'Derikho Nabima S.N',
            'email' => 'derikho@gmail.com',
            'nim' => '244107060050',
            'study_program' => 'D-IV SIB - Polinema',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_fails_if_password_confirmation_does_not_match(): void
    {
        $response = $this->post('/register/mahasiswa', [
            'name' => 'Derikho Nabima S.N',
            'email' => 'derikho@mahasiswa.polinema.ac.id',
            'nim' => '244107060050',
            'study_program' => 'D-IV SIB - Polinema',
            'password' => 'secret123',
            'password_confirmation' => 'different123',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_registration_fails_if_email_or_nim_is_not_unique(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'derikho@mahasiswa.polinema.ac.id',
        ]);

        Student::create([
            'user_id' => $existingUser->id,
            'nim' => '244107060050',
            'study_program' => 'D-IV SIB - Polinema',
        ]);

        $response = $this->post('/register/mahasiswa', [
            'name' => 'Mahasiswa Lain',
            'email' => 'derikho@mahasiswa.polinema.ac.id',
            'nim' => '244107060050',
            'study_program' => 'D-IV SIB - Polinema',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['email', 'nim']);
        $this->assertGuest();
    }
}
