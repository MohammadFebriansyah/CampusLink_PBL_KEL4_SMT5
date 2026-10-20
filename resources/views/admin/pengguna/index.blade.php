@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
    @php
        $warnaPeran = ['mahasiswa' => 'bg-primary-wash text-primary', 'dosen' => 'bg-accent-wash text-accent-dark', 'mitra' => 'bg-[#f3e8ff] text-[#7e22ce]', 'admin' => 'bg-mist text-graphite'];
    @endphp
    @include('admin._kepala', ['judul' => 'Pengguna', 'deskripsi' => 'Cari akun, lihat detail, ubah identitas, atau nonaktifkan.', 'aktif' => 'pengguna'])

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto max-w-page px-4 py-8 sm:px-6 md:py-10">
            <form method="GET" action="{{ route('admin.pengguna.index') }}" role="search"
                  class="flex flex-col gap-2 rounded-3xl bg-paper-white p-2 ring-1 ring-fog sm:flex-row sm:items-center sm:rounded-full">
                <div class="flex flex-1 items-center gap-2 px-3">
                    <svg class="size-5 shrink-0 text-ash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <label for="q" class="sr-only">Nama atau email</label>
                    <input id="q" name="q" type="search" maxlength="100" value="{{ $filter['q'] ?? '' }}" placeholder="Cari nama atau email" class="w-full bg-transparent py-2.5 placeholder:text-ash focus:outline-none">
                </div>
                <label for="peran" class="sr-only">Peran</label>
                <select id="peran" name="peran" class="rounded-full bg-mist px-4 py-2.5 text-sm font-medium">
                    <option value="">Semua peran</option>
                    @foreach (['mahasiswa' => 'Mahasiswa', 'dosen' => 'Dosen', 'mitra' => 'Mitra'] as $nilai => $nama)
                        <option value="{{ $nilai }}" @selected(($filter['peran'] ?? null) === $nilai)>{{ $nama }}</option>
                    @endforeach
                </select>
                <label for="akun" class="sr-only">Status akun</label>
                <select id="akun" name="akun" class="rounded-full bg-mist px-4 py-2.5 text-sm font-medium">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected(($filter['akun'] ?? null) === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(($filter['akun'] ?? null) === 'nonaktif')>Nonaktif</option>
                </select>
                <button type="submit" class="btn btn-primary px-7 py-2.5">Cari</button>
            </form>

            @if ($errors->any())
                <p class="mt-3 text-sm text-ember" role="alert">{{ $errors->first() }}</p>
            @endif

            <p class="mt-6 text-sm font-semibold text-carbon" aria-live="polite">{{ $pengguna->total() }} pengguna</p>

            <div class="relative mt-3 overflow-x-auto rounded-2xl border border-fog bg-paper-white">
                <table class="w-full min-w-[44rem] text-left text-sm">
                    <thead class="border-b border-fog bg-[#fafbfd] text-[11px] font-semibold tracking-wider text-graphite uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3">Nama</th>
                            <th scope="col" class="px-5 py-3">Peran</th>
                            <th scope="col" class="px-5 py-3">Status</th>
                            <th scope="col" class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-fog">
                        @forelse ($pengguna as $u)
                            @php
                                $profil = $u->role === 'dosen' ? $u->dosen : $u->mahasiswa;
                                $inisial = mb_strtoupper(collect(preg_split('/\s+/', trim($u->name)))->filter()->take(2)->map(fn ($k) => mb_substr($k, 0, 1))->join(''));
                            @endphp
                            <tr class="transition hover:bg-[#fafbfd]">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-navy text-[11px] font-bold text-accent" aria-hidden="true">{{ $inisial }}</span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-carbon">{{ $u->name }}</p>
                                            <p class="truncate text-graphite">{{ $u->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $warnaPeran[$u->role] ?? 'bg-mist text-graphite' }}">{{ ucfirst($u->role) }}</span>
                                        @if ($u->terverifikasiGoogle())
                                            <span class="rounded-full bg-mint-wash px-2.5 py-0.5 text-[11px] font-semibold text-[#15803d]">Google</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $u->status === 'aktif' ? 'bg-mint-wash text-[#15803d]' : 'bg-[#fee2e2] text-[#b91c1c]' }}">
                                            <span class="size-1.5 rounded-full {{ $u->status === 'aktif' ? 'bg-[#16a34a]' : 'bg-[#dc2626]' }}" aria-hidden="true"></span>
                                            {{ $u->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                        @if ($u->role === 'mitra' && $u->mitra && $u->mitra->status_verifikasi !== 'disetujui')
                                            <span class="rounded-full bg-accent-wash px-2.5 py-0.5 text-[11px] font-semibold text-accent-dark">{{ $u->mitra->status_verifikasi === 'pending' ? 'Menunggu verifikasi' : 'Ditolak' }}</span>
                                        @endif
                                        @if ($profil && $profil->disembunyikan_admin)
                                            <span class="rounded-full bg-mist px-2.5 py-0.5 text-[11px] font-semibold text-graphite">Profil disembunyikan</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('admin.pengguna.show', $u->id) }}" class="inline-flex items-center rounded-full border border-fog px-3.5 py-1.5 text-sm font-medium transition hover:border-accent/50 hover:text-accent-dark">Kelola</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-14 text-center font-semibold">Tidak ada pengguna yang cocok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('partials.pager', ['paginator' => $pengguna])
        </div>
    </section>
@endsection
