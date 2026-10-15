<footer class="{{ request()->routeIs('home') ? '' : 'mt-16' }} border-t border-fog bg-linen">
    <div class="mx-auto grid max-w-page gap-8 px-4 py-12 sm:px-6 md:grid-cols-4">
        <div class="md:col-span-2">
            <p class="text-subheading font-bold tracking-[-0.4px]">Campus<span class="text-accent">Link</span></p>
            <p class="mt-2 max-w-sm text-graphite">
                Informasi magang, lomba, dan beasiswa untuk mahasiswa Politeknik Negeri Malang dalam satu tempat.
            </p>
        </div>
        <div>
            <p class="font-medium">Informasi</p>
            <ul class="mt-3 space-y-2 text-graphite">
                <li><a href="{{ Route::has('magang.index') ? route('magang.index') : url('/magang') }}" class="hover:text-primary hover:underline">Magang</a></li>
                <li><a href="{{ Route::has('lomba.index') ? route('lomba.index') : url('/lomba') }}" class="hover:text-primary hover:underline">Lomba</a></li>
                <li><a href="{{ Route::has('beasiswa.index') ? route('beasiswa.index') : url('/beasiswa') }}" class="hover:text-primary hover:underline">Beasiswa</a></li>
            </ul>
        </div>
        <div>
            <p class="font-medium">Pengguna</p>
            <ul class="mt-3 space-y-2 text-graphite">
                <li><a href="{{ Route::has('direktori.index') ? route('direktori.index') : url('/direktori') }}" class="hover:text-primary hover:underline">Direktori</a></li>
                <li><a href="{{ route('login') }}" class="hover:text-primary hover:underline">Masuk</a></li>
                <li><a href="{{ route('register') }}" class="hover:text-primary hover:underline">Daftar</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-fog">
        <p class="mx-auto max-w-page px-4 py-4 text-caption text-ash sm:px-6">
            &copy; 2026 Tim CampusLink, Politeknik Negeri Malang. Pendaftaran kegiatan dilakukan di situs penyelenggara.
        </p>
    </div>
</footer>
