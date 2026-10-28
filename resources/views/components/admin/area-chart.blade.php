{{--
    Grafik area SVG yang digambar di server (tanpa library grafik). Kurva dihaluskan dengan kontrol
    horizontal di tengah tiap ruas, jadi tidak pernah melewati nilai puncak/lembah data.
    Seri dibedakan lewat bentuk (putus-putus vs penuh berisi), bukan hanya warna, agar terbaca di kedua tema.
    Props: data (daftar baris), x (kunci label sumbu X), series (daftar ['key', 'label', 'color', 'dashed' => bool]),
           label (ringkasan untuk pembaca layar)
--}}
@props(['data', 'x', 'series', 'label'])

@php
    $lebar = 600;
    $tinggi = 220;
    $atas = 8; // ruang agar garis di nilai puncak tidak terpotong
    $n = count($data);
    $puncak = max(1, ...array_map(fn ($s) => max(array_column($data, $s['key']) ?: [0]), $series));
    $batasY = max(4, (int) ceil($puncak / 4) * 4);

    $titikX = fn (int $i) => $n > 1 ? $i / ($n - 1) * $lebar : $lebar / 2;
    $titikY = fn ($v) => $atas + ($tinggi - $atas) * (1 - $v / $batasY);
    $persenX = fn (int $i) => $n > 1 ? $i / ($n - 1) * 100 : 50;
    $persenY = fn ($v) => $titikY($v) / $tinggi * 100;

    $kurva = function (string $kunci) use ($data, $n, $titikX, $titikY) {
        $d = 'M' . $titikX(0) . ',' . $titikY($data[0][$kunci]);
        for ($i = 1; $i < $n; $i++) {
            $tengah = ($titikX($i - 1) + $titikX($i)) / 2;
            $d .= " C{$tengah}," . $titikY($data[$i - 1][$kunci]) . " {$tengah}," . $titikY($data[$i][$kunci]) . ' ' . $titikX($i) . ',' . $titikY($data[$i][$kunci]);
        }

        return $d;
    };
    $idGradien = 'gradien-' . \Illuminate\Support\Str::random(6);
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    <div class="flex gap-2">
        {{-- Sumbu Y --}}
        <div class="relative w-7 shrink-0 text-right text-xs text-muted-foreground tabular-nums" style="height: {{ $tinggi }}px" aria-hidden="true">
            @foreach ([0, 1, 2, 3, 4] as $k)
                <span class="absolute right-0 -translate-y-1/2" style="top: {{ $persenY($batasY / 4 * $k) }}%">{{ $batasY / 4 * $k }}</span>
            @endforeach
        </div>

        <div class="relative min-w-0 flex-1" x-data="{ i: null }"
             @mousemove="i = Math.round(($event.clientX - $el.getBoundingClientRect().left) / $el.offsetWidth * {{ max($n - 1, 0) }})"
             @mouseleave="i = null">
            <svg viewBox="0 0 {{ $lebar }} {{ $tinggi }}" preserveAspectRatio="none" class="block w-full overflow-visible" style="height: {{ $tinggi }}px"
                 role="img" aria-label="{{ $label }}">
                <defs>
                    @foreach ($series as $s)
                        @unless ($s['dashed'] ?? false)
                            <linearGradient id="{{ $idGradien }}-{{ $s['key'] }}" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="{{ $s['color'] }}" stop-opacity="0.25" />
                                <stop offset="100%" stop-color="{{ $s['color'] }}" stop-opacity="0.02" />
                            </linearGradient>
                        @endunless
                    @endforeach
                </defs>
                @foreach ([1, 2, 3, 4] as $k)
                    <line x1="0" x2="{{ $lebar }}" y1="{{ $titikY($batasY / 4 * $k) }}" y2="{{ $titikY($batasY / 4 * $k) }}"
                          stroke="var(--border)" stroke-dasharray="3 3" vector-effect="non-scaling-stroke" />
                @endforeach
                <line x1="0" x2="{{ $lebar }}" y1="{{ $tinggi }}" y2="{{ $tinggi }}" stroke="var(--border)" vector-effect="non-scaling-stroke" />
                @foreach ($series as $s)
                    @unless ($s['dashed'] ?? false)
                        <path d="{{ $kurva($s['key']) }} L{{ $titikX($n - 1) }},{{ $tinggi }} L{{ $titikX(0) }},{{ $tinggi }} Z" fill="url(#{{ $idGradien }}-{{ $s['key'] }})" />
                    @endunless
                    <path d="{{ $kurva($s['key']) }}" fill="none" stroke="{{ $s['color'] }}" stroke-width="2" vector-effect="non-scaling-stroke"
                          @if ($s['dashed'] ?? false) stroke-dasharray="4 4" @endif />
                @endforeach
            </svg>

            {{-- Penanda dan tooltip untuk titik yang ditunjuk kursor --}}
            @foreach ($data as $idx => $baris)
                <div x-cloak x-show="i === {{ $idx }}" class="pointer-events-none absolute inset-y-0" style="left: {{ $persenX($idx) }}%" aria-hidden="true">
                    <div class="absolute inset-y-0 w-px bg-border"></div>
                    @foreach ($series as $s)
                        <span class="absolute size-2.5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-card" style="top: {{ $persenY($baris[$s['key']]) }}%; left: 0.5px; background: {{ $s['color'] }}"></span>
                    @endforeach
                    <div class="absolute top-2 z-10 grid min-w-36 gap-1.5 rounded-lg border border-border bg-card px-2.5 py-1.5 text-xs shadow-xl {{ $idx > ($n - 1) / 2 ? 'right-3' : 'left-3' }}">
                        <p class="font-medium">{{ $baris[$x] }}</p>
                        @foreach ($series as $s)
                            <p class="flex items-center justify-between gap-4">
                                <span class="flex items-center gap-1.5 text-muted-foreground"><span class="size-2.5 rounded-[2px]" style="background: {{ $s['color'] }}"></span>{{ $s['label'] }}</span>
                                <span class="font-mono font-medium tabular-nums">{{ $baris[$s['key']] }}</span>
                            </p>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Sumbu X --}}
            <div class="relative mt-2 h-4 text-xs text-muted-foreground" aria-hidden="true">
                @foreach ($data as $idx => $baris)
                    <span class="absolute whitespace-nowrap {{ $idx === 0 ? '' : ($idx === $n - 1 ? '-translate-x-full' : '-translate-x-1/2') }} {{ $idx % 2 === 1 && $idx !== $n - 1 ? 'hidden sm:inline' : '' }}" style="left: {{ $persenX($idx) }}%">{{ $baris[$x] }}</span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Legenda --}}
    <div class="mt-4 flex flex-wrap items-center justify-center gap-4 text-xs">
        @foreach ($series as $s)
            <span class="flex items-center gap-1.5">
                @if ($s['dashed'] ?? false)
                    <span class="h-0 w-4 border-t-2 border-dashed" style="border-color: {{ $s['color'] }}"></span>
                @else
                    <span class="h-0.5 w-4 rounded-full" style="background: {{ $s['color'] }}"></span>
                @endif
                {{ $s['label'] }}
            </span>
        @endforeach
    </div>

    {{-- Data yang sama dalam bentuk tabel untuk pembaca layar --}}
    <table class="sr-only">
        <caption>{{ $label }}</caption>
        <thead><tr><th scope="col">{{ ucfirst($x) }}</th>@foreach ($series as $s)<th scope="col">{{ $s['label'] }}</th>@endforeach</tr></thead>
        <tbody>
            @foreach ($data as $baris)
                <tr><th scope="row">{{ $baris[$x] }}</th>@foreach ($series as $s)<td>{{ $baris[$s['key']] }}</td>@endforeach</tr>
            @endforeach
        </tbody>
    </table>
</div>
