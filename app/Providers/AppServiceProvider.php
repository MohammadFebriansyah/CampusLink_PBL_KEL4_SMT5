<?php

namespace App\Providers;

use App\Models\Beasiswa;
use App\Models\Lomba;
use App\Models\Magang;
use App\Policies\BeasiswaPolicy;
use App\Policies\LombaPolicy;
use App\Policies\MagangPolicy;
use App\Support\Cari;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
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
        Builder::macro('whereMengandung', function (string $kolom, string $kata, string $boolean = 'and'): Builder {
            return Cari::mengandung($this, $kolom, $kata, $boolean);
        });
        Builder::macro('orWhereMengandung', function (string $kolom, string $kata): Builder {
            return $this->whereMengandung($kolom, $kata, 'or');
        });

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

        $this->catatKejadianPenting();
    }

    /**
     * Log untuk kejadian di luar alur exception biasa: login gagal, akun terkunci, job antrean gagal,
     * dan tugas terjadwal gagal. Request ID ikut tercatat otomatis (Context diteruskan ke job).
     */
    private function catatKejadianPenting(): void
    {
        Event::listen(Failed::class, function (Failed $e): void {
            // Email di-hash (bukan ditulis apa adanya) supaya percobaan berulang bisa dikenali tanpa menyimpan data pribadi.
            $email = strtolower((string) ($e->credentials['email'] ?? ''));
            Log::warning('Login gagal', [
                'guard' => $e->guard,
                'email_hash' => $email === '' ? null : substr(hash('sha256', $email), 0, 12),
                'akun_dikenal' => $e->user !== null,
            ]);
        });

        Event::listen(Lockout::class, function (Lockout $e): void {
            Log::warning('Login dikunci karena terlalu banyak percobaan', ['status_code' => 429]);
        });

        Event::listen(JobFailed::class, function (JobFailed $e): void {
            Log::error('Job antrean gagal: '.$e->job->resolveName(), [
                'connection' => $e->connectionName,
                'queue' => $e->job->getQueue(),
                'exception' => $e->exception,
            ]);
        });

        Event::listen(ScheduledTaskFailed::class, function (ScheduledTaskFailed $e): void {
            Log::error('Tugas terjadwal gagal: '.($e->task->description ?: $e->task->command ?: 'closure'), [
                'exception' => $e->exception,
            ]);
        });
    }
}
