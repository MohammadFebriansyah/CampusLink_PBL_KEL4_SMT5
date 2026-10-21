<?php

namespace App\Providers;

use App\Models\Beasiswa;
use App\Models\Lomba;
use App\Models\Magang;
use App\Policies\BeasiswaPolicy;
use App\Policies\LombaPolicy;
use App\Policies\MagangPolicy;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Alias pendek untuk kolom informasi_type di tabel pendaftaran dan bimbingan.
        // Sama dengan nama kategori di route, jadi mudah dipetakan bolak-balik.
        Relation::morphMap([
            'magang' => Magang::class,
            'lomba' => Lomba::class,
            'beasiswa' => Beasiswa::class,
        ]);

        Gate::policy(Magang::class, MagangPolicy::class);
        Gate::policy(Lomba::class, LombaPolicy::class);
        Gate::policy(Beasiswa::class, BeasiswaPolicy::class);
    }
}
