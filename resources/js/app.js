import Alpine from 'alpinejs';
import combobox from './combobox';
import daftar from './daftar';

// <select x-data x-combobox>: pilihan dengan kolom pencarian (lihat resources/js/combobox.js).
Alpine.plugin(combobox);
// x-data="daftar(...)": daftar dari JSON dengan pencarian, pengurutan, dan paginasi async (resources/js/daftar.js).
Alpine.plugin(daftar);

// Angka yang menghitung naik dari 0 ke nilai aslinya (dipakai di tombol kategori beranda).
Alpine.data('hitung', (target) => ({
    n: target,
    init() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || target === 0) return;
        this.n = 0;
        const mulai = performance.now();
        const durasi = 1200;
        const langkah = (waktu) => {
            const p = Math.min((waktu - mulai) / durasi, 1);
            this.n = Math.round(target * (1 - Math.pow(1 - p, 3)));
            if (p < 1) requestAnimationFrame(langkah);
        };
        requestAnimationFrame(langkah);
    },
}));

// Panel detail di samping daftar informasi (layar lebar). Di layar kecil, kartu tetap membuka halaman detail.
Alpine.data('panelDetail', (awal) => ({
    aktif: awal,
    memuatDetail: false,
    buka(event, id) {
        const lebar = window.matchMedia('(min-width: 1024px)').matches;
        // Biarkan Ctrl/Cmd+klik dan klik tengah membuka tab baru seperti biasa.
        if (!lebar || event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) return;
        event.preventDefault();
        this.bukaUrl(event.currentTarget.href, id);
    },
    // Daftar dimuat ulang (cari/urut/halaman): bila yang terbuka tidak ada lagi, buka hasil pertama.
    setelahDimuat(items, kunci = (i) => i.id) {
        if (!window.matchMedia('(min-width: 1024px)').matches) return;
        const bisa = items.filter((i) => i.url);
        if (bisa.length && !bisa.some((i) => kunci(i) === this.aktif)) this.bukaUrl(bisa[0].url, kunci(bisa[0]), true);
    },
    // otomatis: dibuka tanpa klik pengguna, jadi bila gagal jangan pindah halaman.
    async bukaUrl(url, id, otomatis = false) {
        if (id === this.aktif || this.memuatDetail || !this.$refs.isi) return;
        const sebelumnya = this.aktif;
        this.aktif = id;
        this.memuatDetail = true;
        try {
            const respons = await fetch(url, { headers: { 'X-Panel': '1' } });
            // Sesi habis (dialihkan ke halaman masuk) atau galat: buka halamannya langsung.
            if (!respons.ok || respons.redirected) throw new Error('gagal');
            this.$refs.isi.innerHTML = await respons.text();
            this.$refs.panel.scrollTop = 0;
            const alamat = new URL(window.location.href);
            alamat.searchParams.set('pilih', id);
            history.replaceState(null, '', alamat);
        } catch {
            if (otomatis) this.aktif = sebelumnya;
            else window.location.href = url;
        } finally {
            this.memuatDetail = false;
        }
    },
}));

// TAMBAHAN: $fokus(elemen atau id) memindahkan fokus ke elemen yang baru ditampilkan x-show.
// x-show menampilkan elemen lewat setTimeout setelah $nextTick, jadi fokus dijadwalkan sesudahnya.
Alpine.magic('fokus', () => (target) => {
    Alpine.nextTick(() => setTimeout(() => {
        (typeof target === 'string' ? document.getElementById(target) : target)?.focus();
    }));
});

// Form riwayat profil (sertifikasi, pelatihan, proyek, pencapaian): dikirim tanpa pindah halaman,
// supaya isian lain di halaman profil yang belum disimpan tidak hilang.
// Server membalas HTML bagian yang sudah diperbarui; galat validasi (422) ditampilkan di form.
Alpine.data('riwayatForm', (galatAwal = {}) => ({
    galat: galatAwal,
    gagal: '',
    mengirim: false,
    async kirim(event) {
        if (this.mengirim) return;
        const form = event.target;
        this.mengirim = true;
        this.gagal = '';
        try {
            const respons = await fetch(form.action, {
                method: 'POST', // _method (PUT/DELETE) ikut di FormData
                body: new FormData(form),
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (respons.status === 422) {
                this.galat = (await respons.json()).errors ?? {};
                this.$nextTick(() => form.querySelector('[aria-invalid="true"]')?.focus());
                return;
            }
            if (!respons.ok) throw new Error('gagal');
            const { html } = await respons.json();
            const bagian = form.closest('[data-riwayat]');
            const id = bagian.id;
            bagian.outerHTML = html;
            // Fokus ke judul bagian agar pembaca layar mendengar pesan status yang baru.
            document.getElementById(`${id}-judul`)?.focus();
        } catch {
            this.gagal = 'Gagal menyimpan. Periksa koneksi lalu coba lagi, atau muat ulang halaman.';
        } finally {
            this.mengirim = false;
        }
    },
}));

// Panel admin: tema terang/gelap dan sidebar. Kelas .dark dan data-sidebar sudah dipasang skrip kecil
// di <head> layouts/admin sebelum CSS dimuat, jadi halaman tidak berkedip; di sini hanya diganti dan disimpan.
const KUNCI_TEMA = 'campuslink-tema';
const KUNCI_SIDEBAR = 'campuslink-sidebar';
const simpan = (kunci, nilai) => {
    try {
        localStorage.setItem(kunci, nilai);
    } catch {
        // Penyimpanan diblokir (mode privat): pilihan hanya berlaku sampai halaman dimuat ulang.
    }
};

Alpine.data('tema', () => ({
    gelap: document.documentElement.classList.contains('dark'),
    init() {
        // Selama pengguna belum memilih sendiri, ikuti perubahan tema sistem.
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            let tersimpan = null;
            try {
                tersimpan = localStorage.getItem(KUNCI_TEMA);
            } catch {}
            if (tersimpan === null) this.terapkan(e.matches);
        });
    },
    ganti() {
        this.terapkan(!this.gelap);
        simpan(KUNCI_TEMA, this.gelap ? 'gelap' : 'terang');
    },
    terapkan(gelap) {
        this.gelap = gelap;
        document.documentElement.classList.toggle('dark', gelap);
    },
}));

// Layar lebar: sidebar tetap dan bisa diciutkan (diingat). Layar sempit: drawer geser.
Alpine.data('panelAdmin', () => ({
    menu: false,
    lebar: window.matchMedia('(min-width: 1024px)').matches,
    ciut: document.documentElement.dataset.sidebar === 'tutup',
    get terbuka() {
        return this.lebar ? !this.ciut : this.menu;
    },
    ukur() {
        this.lebar = window.matchMedia('(min-width: 1024px)').matches;
        if (this.lebar) this.menu = false;
    },
    alih() {
        if (!this.lebar) {
            this.menu = !this.menu;
            return;
        }
        this.ciut = !this.ciut;
        document.documentElement.dataset.sidebar = this.ciut ? 'tutup' : 'buka';
        simpan(KUNCI_SIDEBAR, this.ciut ? 'tutup' : 'buka');
    },
}));

window.Alpine = Alpine;
Alpine.start();
