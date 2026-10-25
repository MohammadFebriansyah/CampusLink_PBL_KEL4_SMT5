import Alpine from 'alpinejs';

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
    memuat: false,
    async buka(event, id) {
        const lebar = window.matchMedia('(min-width: 1024px)').matches;
        // Biarkan Ctrl/Cmd+klik dan klik tengah membuka tab baru seperti biasa.
        if (!lebar || event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) return;
        event.preventDefault();
        if (id === this.aktif || this.memuat) return;

        const url = event.currentTarget.href;
        this.aktif = id;
        this.memuat = true;
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
            window.location.href = url;
        } finally {
            this.memuat = false;
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

window.Alpine = Alpine;
Alpine.start();
