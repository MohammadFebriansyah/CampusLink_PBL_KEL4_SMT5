<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

/** Setelah login Fortify: kembali ke halaman yang dituju, atau ke halaman utama peran pengguna. */
class LoginResponse implements LoginResponseContract
{
    /**
     * @param  Request  $request
     */
    public function toResponse($request): RedirectResponse
    {
        $rute = $request->user()->panel()['rute'];

        return redirect()->intended(Route::has($rute) ? route($rute) : route('home'));
    }
}
