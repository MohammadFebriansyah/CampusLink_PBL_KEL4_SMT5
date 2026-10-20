{{-- Menu panel admin. Parameter: aktif (kunci menu), gelap (opsional, true bila di atas latar navy) --}}
@php
    $menu = [
        'dashboard' => ['Dasbor', route('admin.dashboard')],
        'mitra' => ['Verifikasi mitra', route('admin.mitra.index')],
        'pendaftar' => ['Pendaftar', route('admin.pendaftar.index')],
        'pengguna' => ['Pengguna', route('admin.pengguna.index')],
        'magang' => ['Magang', route('admin.informasi.index', 'magang')],
        'lomba' => ['Lomba', route('admin.informasi.index', 'lomba')],
        'beasiswa' => ['Beasiswa', route('admin.informasi.index', 'beasiswa')],
        'keahlian' => ['Keahlian', route('admin.keahlian.index')],
    ];
    $gelap = $gelap ?? false;
@endphp
<nav class="mt-6 -mx-1 overflow-x-auto px-1 pb-1" aria-label="Menu admin">
    <div class="inline-flex gap-1 rounded-full p-1 {{ $gelap ? 'bg-white/10 ring-1 ring-white/15' : 'bg-paper-white ring-1 ring-fog' }}">
        @foreach ($menu as $kunci => [$nama, $url])
            <a href="{{ $url }}"
               class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold whitespace-nowrap transition {{ $aktif === $kunci ? 'bg-accent text-navy' : ($gelap ? 'text-white/75 hover:bg-white/10 hover:text-paper-white' : 'text-graphite hover:bg-mist hover:text-carbon') }}"
               @if ($aktif === $kunci) aria-current="page" @endif>{{ $nama }}</a>
        @endforeach
    </div>
</nav>
