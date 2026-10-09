<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterStudentRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterStudentController extends Controller
{
    /**
     * Display the student registration view.
     */
    public function create(): View
    {
        return view('auth.register-mahasiswa');
    }

    /**
     * Handle an incoming student registration request.
     */
    public function store(RegisterStudentRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'student',
            ]);

            Student::create([
                'user_id' => $user->id,
                'nim' => $request->nim,
                'study_program' => $request->study_program,
                'is_pddikti_verified' => true,
                'pddikti_verified_at' => now(),
            ]);

            return $user;
        });

        Auth::login($user);

        return redirect('/')->with('status', 'Registrasi Akun Mahasiswa berhasil! Selamat datang di CampusLink.');
    }
}
