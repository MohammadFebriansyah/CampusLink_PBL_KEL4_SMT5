// Daftar yang dirender Alpine dari JSON: pencarian (debounce), filter, pengurutan, dan paginasi tanpa reload.
//
// Backend (App\Support\Daftar) melayani URL halaman yang sama dalam dua bentuk: halaman Blade berisi data awal
// di <script type="application/json">, dan JSON bila diminta dengan "Accept: application/json".
// Bentuk JSON: { data: [...], meta: { current_page, last_page, total, from, to, ... }, filter: {...}, tambahan: {...} }
//
// Pemakaian di Blade:
//   <x-daftar.data id="data-pengguna" :nilai="$awal" />
//   <div x-data="daftar({ sumber: 'data-pengguna' })"> ... <template x-for="item in items"> ... </div>
// Opsi: sumber (id elemen data awal, wajib), url (bawaan: path halaman), bukanFilter (kunci yang tidak ikut
// "atur ulang", mis. tab status), gulirKe (id elemen yang dituju saat pindah halaman), sinkronUrl (bawaan true).

const JEDA_KETIK = 350; // ms setelah berhenti mengetik sebelum mencari

export default function daftar(Alpine) {
    Alpine.data('daftar', (opsi = {}) => ({
        url: opsi.url ?? window.location.pathname,
        items: [],
        meta: { current_page: 1, last_page: 1, total: 0, from: null, to: null },
        filter: {},
        tambahan: {},
        memuat: false,
        galat: '',
        // Filter bawaan (nilai kosong) untuk tombol "atur ulang"; urut/arah kembali ke bawaan server.
        _awalFilter: {},
        _pengendali: null,
        _jedaKetik: null,
        _terjadwal: false,

        init() {
            const awal = JSON.parse(document.getElementById(opsi.sumber).textContent);
            this.terapkan(awal);
            // Filter dari server hanya dipakai sekali di sini. Setelah itu masukan pengguna yang menjadi sumber,
            // agar nilai yang dirapikan server (mis. spasi di akhir kata kunci) tidak menimpa yang sedang diketik.
            this.filter = Object.fromEntries(Object.entries(awal.filter ?? {}).map(([k, v]) => [k, v ?? '']));
            this._awalFilter = Object.fromEntries(Object.keys(this.filter).map((k) => [k, '']));

            // Kata kunci menunggu jeda ketik; filter lain langsung memuat ulang dari halaman 1.
            for (const kunci of Object.keys(this.filter)) {
                this.$watch(`filter.${kunci}`, () => {
                    if (kunci === 'q') {
                        clearTimeout(this._jedaKetik);
                        this._jedaKetik = setTimeout(() => this.muat(1), JEDA_KETIK);
                    } else {
                        this.jadwalkan();
                    }
                });
            }
        },

        // Beberapa filter bisa berubah sekaligus (mis. urut dan arah): satu permintaan saja.
        jadwalkan() {
            if (this._terjadwal) return;
            this._terjadwal = true;
            queueMicrotask(() => {
                this._terjadwal = false;
                this.muat(1);
            });
        },

        terapkan(respons) {
            this.items = respons.data ?? [];
            this.meta = respons.meta ?? this.meta;
            this.tambahan = respons.tambahan ?? {};
        },

        parameter(halaman) {
            const p = new URLSearchParams();
            for (const [k, v] of Object.entries(this.filter)) {
                if (v !== '' && v !== null && v !== undefined) p.set(k, v);
            }
            if (halaman > 1) p.set('page', halaman);
            return p;
        },

        async muat(halaman = 1) {
            clearTimeout(this._jedaKetik);
            // Permintaan sebelumnya dibatalkan: hasil yang tampil selalu milik masukan terakhir.
            this._pengendali?.abort();
            const pengendali = (this._pengendali = new AbortController());
            const p = this.parameter(halaman);
            const alamat = p.toString() ? `${this.url}?${p}` : this.url;

            this.memuat = true;
            this.galat = '';
            try {
                const respons = await fetch(alamat, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: pengendali.signal,
                });
                // Sesi habis atau akses dicabut: buka halamannya langsung agar diarahkan dengan benar.
                if (respons.redirected || [401, 403, 419].includes(respons.status)) {
                    window.location.href = alamat;
                    return;
                }
                if (respons.status === 422) {
                    const { errors = {} } = await respons.json();
                    this.galat = Object.values(errors)[0]?.[0] ?? 'Masukan pencarian tidak valid.';
                    return;
                }
                if (!respons.ok) throw new Error(`HTTP ${respons.status}`);

                this.terapkan(await respons.json());
                if (opsi.sinkronUrl !== false) history.replaceState(history.state, '', alamat);
                this.$dispatch('daftar-dimuat', { items: this.items });
            } catch (e) {
                if (e.name !== 'AbortError') this.galat = 'Gagal memuat data. Periksa koneksi lalu coba lagi.';
            } finally {
                if (this._pengendali === pengendali) this.memuat = false;
            }
        },

        keHalaman(n) {
            if (n < 1 || n > this.meta.last_page || n === this.meta.current_page) return;
            this.muat(n);
            if (opsi.gulirKe) document.getElementById(opsi.gulirKe)?.scrollIntoView({ block: 'start', behavior: 'smooth' });
        },

        // Klik judul kolom: kolom yang sama membalik arah; kolom baru memakai arah bawaannya.
        urutkan(kunci, arahAwal = 'asc') {
            if (this.filter.urut === kunci) {
                this.filter.arah = this.filter.arah === 'asc' ? 'desc' : 'asc';
            } else {
                this.filter.urut = kunci;
                this.filter.arah = arahAwal;
            }
        },

        arahKolom(kunci) {
            if (this.filter.urut !== kunci) return 'none';
            return this.filter.arah === 'desc' ? 'descending' : 'ascending';
        },

        // Pilihan urutan berbentuk "kunci:arah" untuk <select x-model="urutan">.
        get urutan() {
            return `${this.filter.urut}:${this.filter.arah}`;
        },
        set urutan(nilai) {
            const [urut, arah] = String(nilai).split(':');
            this.filter.urut = urut;
            this.filter.arah = arah;
        },

        get adaFilter() {
            return Object.entries(this.filter).some(([k, v]) => !['urut', 'arah', 'per_halaman', ...(opsi.bukanFilter ?? [])].includes(k) && v !== '' && v !== null);
        },

        aturUlang() {
            for (const k of Object.keys(this.filter)) {
                if (!['urut', 'arah', 'per_halaman', ...(opsi.bukanFilter ?? [])].includes(k)) this.filter[k] = this._awalFilter[k];
            }
        },

        // Nomor halaman yang ditampilkan: pertama, terakhir, dan sekitar halaman aktif; celah diberi "…".
        get nomorHalaman() {
            const { current_page: kini, last_page: akhir } = this.meta;
            const nomor = new Set([1, akhir, kini - 1, kini, kini + 1].filter((n) => n >= 1 && n <= akhir));
            const urut = [...nomor].sort((a, b) => a - b);
            const hasil = [];
            urut.forEach((n, i) => {
                if (i > 0 && n - urut[i - 1] > 1) hasil.push(`celah-${n}`);
                hasil.push(n);
            });
            return hasil;
        },
    }));
}
