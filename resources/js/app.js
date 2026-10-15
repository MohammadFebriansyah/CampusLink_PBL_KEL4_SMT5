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

window.Alpine = Alpine;
Alpine.start();
