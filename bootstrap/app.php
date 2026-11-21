<?php

use App\Http\Middleware\CatatGalatHttp;
use App\Http\Middleware\IdPermintaan;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // IdPermintaan paling awal supaya semua log (termasuk dari middleware lain) membawa Request ID.
        $middleware->prepend(IdPermintaan::class);
        $middleware->append(SecurityHeaders::class);
        $middleware->append(CatatGalatHttp::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Tandai permintaan yang exception-nya sudah dilaporkan, agar CatatGalatHttp tidak mencatat ganda.
        $exceptions->report(function (Throwable $e): void {
            request()->attributes->set('galat_dilaporkan', true);
        });

        // Request ID juga dikirim pada respons galat (halaman 4xx/5xx dibuat di luar alur middleware biasa).
        $exceptions->respond(function (Response $respons, Throwable $e, Request $request) {
            if ($id = $request->attributes->get('request_id')) {
                $respons->headers->set(IdPermintaan::HEADER, $id);
            }

            return $respons;
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
