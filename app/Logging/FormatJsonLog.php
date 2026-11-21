<?php

namespace App\Logging;

use Illuminate\Support\Facades\Context;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Satu baris JSON per entri log, dengan bidang yang sama untuk semua jenis log:
 * timestamp, level, environment, request_id, method, url, status_code, user_id, message,
 * exception, file, line, trace, dan context tambahan (sudah disamarkan).
 *
 * Yang tidak pernah dicatat: isi permintaan (body), cookie, header Authorization, sesi, dan query string URL.
 */
class FormatJsonLog implements FormatterInterface
{
    private const BATAS_TRACE = 40; // jumlah frame stack trace yang dicatat

    private NormalizerFormatter $normalizer;

    public function __construct()
    {
        $this->normalizer = new NormalizerFormatter;
    }

    public function format(LogRecord $record): string
    {
        $konteks = $record->context;
        $galat = $konteks['exception'] ?? null;
        unset($konteks['exception']);

        $statusKonteks = $konteks['status_code'] ?? null;
        unset($konteks['status_code']);

        $entri = [
            'timestamp' => $record->datetime->format('c'),
            'level' => strtoupper($record->level->name),
            'environment' => config('app.env'),
            'request_id' => $this->idPermintaan(),
        ] + $this->dataPermintaan() + [
            'status_code' => $statusKonteks ?? ($galat instanceof Throwable ? $this->statusDari($galat) : null),
            'message' => $this->pesan($record->message, $galat),
        ];

        if ($galat instanceof Throwable) {
            $entri += [
                'exception' => $galat::class,
                'exception_message' => Sanitasi::pesanException($galat),
                'file' => $this->jalurRelatif($galat->getFile()),
                'line' => $galat->getLine(),
                'sql' => Sanitasi::sqlAman($galat),
                'trace' => $this->trace($galat),
            ];
        }

        $sisa = Sanitasi::bersihkan((array) $this->normalizer->normalizeValue($konteks));
        if ($sisa !== []) {
            $entri['context'] = $sisa;
        }

        return json_encode(
            array_filter($entri, fn ($v) => $v !== null),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE,
        )."\n";
    }

    public function formatBatch(array $records): string
    {
        return implode('', array_map(fn (LogRecord $r) => $this->format($r), $records));
    }

    /**
     * Laravel memakai pesan exception sebagai pesan log. Untuk QueryException pesan itu memuat SQL beserta
     * nilai yang diikat, jadi bagian itu diganti dengan versi yang sudah dibersihkan.
     */
    private function pesan(string $pesan, mixed $galat): string
    {
        if ($galat instanceof Throwable && $galat->getMessage() !== '') {
            $pesan = str_replace($galat->getMessage(), Sanitasi::pesanException($galat), $pesan);
        }

        return Sanitasi::teks($pesan);
    }

    private function idPermintaan(): ?string
    {
        try {
            $id = Context::get('request_id');

            return is_string($id) ? $id : null;
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array<string, mixed> method, url (tanpa query string), dan user_id bila ada permintaan HTTP. */
    private function dataPermintaan(): array
    {
        $app = app();

        if (! $app->bound('request') || ($app->runningInConsole() && ! $app->runningUnitTests())) {
            return [];
        }

        try {
            $request = $app->make('request');

            $data = ['method' => $request->getMethod(), 'url' => '/'.ltrim($request->path(), '/')];

            // hasUser(): tidak memicu query ke database hanya untuk keperluan log.
            $guard = $app->bound('auth') ? $app['auth']->guard() : null;
            if ($guard !== null && method_exists($guard, 'hasUser') && $guard->hasUser()) {
                $data['user_id'] = $guard->id();
            }

            return $data;
        } catch (Throwable) {
            return [];
        }
    }

    private function statusDari(Throwable $e): int
    {
        return $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
    }

    private function jalurRelatif(string $jalur): string
    {
        $dasar = str_replace('\\', '/', base_path()).'/';
        $jalur = str_replace('\\', '/', $jalur);

        return str_starts_with($jalur, $dasar) ? substr($jalur, strlen($dasar)) : $jalur;
    }

    /** Stack trace tanpa argumen fungsi (argumen bisa memuat kata sandi atau token). */
    private function trace(Throwable $e): string
    {
        $baris = [];

        foreach (array_slice($e->getTrace(), 0, self::BATAS_TRACE) as $i => $frame) {
            $lokasi = isset($frame['file']) ? $this->jalurRelatif($frame['file']).'('.($frame['line'] ?? 0).')' : '[internal]';
            $baris[] = '#'.$i.' '.$lokasi.': '.($frame['class'] ?? '').($frame['type'] ?? '').($frame['function'] ?? '').'()';
        }

        return implode("\n", $baris);
    }
}
