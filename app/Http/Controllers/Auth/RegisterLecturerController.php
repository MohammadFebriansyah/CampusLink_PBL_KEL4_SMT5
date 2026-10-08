<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterLecturerRequest;
use App\Models\Lecturer;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterLecturerController extends Controller
{
    /**
     * Display the lecturer registration view.
     */
    public function create(): View
    {
        return view('auth.register-dosen');
    }

    /**
     * Handle an incoming lecturer registration request.
     */
    public function store(RegisterLecturerRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'lecturer',
            ]);

            Lecturer::create([
                'user_id' => $user->id,
                'nidn' => $request->nidn_nip,
                'institution' => $request->homebase,
                'expertise' => $request->expertise,
                'is_pddikti_verified' => true,
                'pddikti_verified_at' => now(),
            ]);

            return $user;
        });

        Auth::login($user);

        return redirect('/')->with('status', 'Registrasi Akun Dosen berhasil! Selamat datang di CampusLink.');
    }
}
