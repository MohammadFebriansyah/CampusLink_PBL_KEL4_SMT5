<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

/** Setelah logout Fortify (sesi sudah diinvalidasi dan token CSRF diganti): ke beranda. */
class LogoutResponse implements LogoutResponseContract
{
    /**
     * @param  Request  $request
     */
    public function toResponse($request): RedirectResponse
    {
        return redirect()->route('home');
    }
}
