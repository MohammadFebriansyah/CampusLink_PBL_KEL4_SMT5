// Combobox dengan pencarian untuk <select> biasa: <select x-combobox ...>.
// <select> asli tetap berada di form (disembunyikan), jadi name, required, nilai awal dari Blade (@selected/old),
// optgroup, dan pengiriman form tidak berubah. Tombol pengganti menyalin kelas <select> agar tampilannya sama
// dengan gaya halaman tempatnya dipakai. Daftar pilihan ditempel ke <body> dengan posisi fixed supaya tidak
// terpotong kartu/tabel yang memakai overflow-hidden.

let urutan = 0;

const normal = (teks) => teks.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();

const IKON_PANAH = '<svg class="size-4 shrink-0 opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m7 15 5 5 5-5M7 9l5-5 5 5"/></svg>';
const IKON_CARI = '<svg class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';
const IKON_CENTANG = '<svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';

export default function combobox(Alpine) {
    Alpine.directive('combobox', (select, _, { cleanup, effect }) => {
        const id = `combobox-${++urutan}`;
        const label = select.labels?.[0];
        if (label && !label.id) label.id = `${id}-label`;

        // ---------- Tombol pengganti ----------
        const tombol = document.createElement('button');
        tombol.type = 'button';
        tombol.id = `${id}-tombol`;
        tombol.setAttribute('aria-haspopup', 'listbox');
        tombol.setAttribute('aria-expanded', 'false');
        tombol.setAttribute('aria-controls', `${id}-daftar`);
        const teksTombol = document.createElement('span');
        teksTombol.className = 'min-w-0 flex-1 truncate';
        tombol.append(teksTombol);
        tombol.insertAdjacentHTML('beforeend', IKON_PANAH);

        // <select> asli: tetap bisa divalidasi browser, tetapi tidak terlihat dan tidak bisa diklik.
        const gayaAwal = select.getAttribute('style');
        select.setAttribute('tabindex', '-1');
        select.setAttribute('aria-hidden', 'true');
        select.style.cssText += ';position:absolute;width:1px;height:1px;opacity:0;pointer-events:none;';
        select.after(tombol);

        // ---------- Panel daftar ----------
        const panel = document.createElement('div');
        panel.className = 'fixed z-[70] hidden rounded-lg border border-border bg-card p-1 text-sm text-foreground shadow-lg';
        panel.innerHTML = `
            <div class="relative mb-1">
                ${IKON_CARI}
                <input type="text" autocomplete="off" spellcheck="false" role="combobox" aria-autocomplete="list" aria-expanded="true"
                       aria-controls="${id}-daftar" placeholder="Cari..."
                       class="h-9 w-full rounded-md border border-input bg-background pr-2 pl-8 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none">
            </div>
            <ul id="${id}-daftar" role="listbox" class="max-h-64 overflow-y-auto overscroll-contain"></ul>
            <p class="hidden px-2 py-6 text-center text-sm text-muted-foreground">Tidak ada hasil.</p>`;
        const cari = panel.querySelector('input');
        const daftar = panel.querySelector('ul');
        const kosong = panel.querySelector('p');
        document.body.append(panel);

        let terbuka = false;
        let pilihanTampil = []; // <li role="option"> yang lolos filter
        let aktif = -1;
        let galatKlien = false; // kolom wajib dikirim kosong (validasi browser)

        const namaAksesibel = () => {
            const dariLabel = label ? label.textContent.trim() : select.getAttribute('aria-label') || '';
            cari.setAttribute('aria-label', `Cari ${dariLabel.toLowerCase()}`.trim());
            if (label) tombol.setAttribute('aria-labelledby', `${label.id} ${tombol.id}`);
            else if (dariLabel) tombol.setAttribute('aria-label', dariLabel);
        };

        // Salin tampilan dan status <select> ke tombol.
        const sinkron = () => {
            tombol.className = `${select.className} flex items-center gap-2 text-left cursor-pointer disabled:cursor-not-allowed disabled:opacity-60${galatKlien ? ' ring-2 ring-ember' : ''}`;
            const terpilih = select.options[select.selectedIndex];
            const kosongkah = !terpilih || (terpilih.value === '' && terpilih.disabled);
            teksTombol.textContent = terpilih ? terpilih.textContent.trim() : '';
            teksTombol.classList.toggle('opacity-60', kosongkah);
            tombol.disabled = select.disabled;
            for (const atribut of ['aria-invalid', 'aria-describedby']) {
                if (select.hasAttribute(atribut)) tombol.setAttribute(atribut, select.getAttribute(atribut));
                else tombol.removeAttribute(atribut);
            }
            if (galatKlien) tombol.setAttribute('aria-invalid', 'true');
            if (select.required) tombol.setAttribute('aria-required', 'true');
            namaAksesibel();
        };

        // Bangun daftar dari <option>/<optgroup>. Opsi placeholder (value kosong + disabled) tidak ditampilkan.
        const bangunDaftar = () => {
            const kata = normal(cari.value);
            daftar.replaceChildren();
            pilihanTampil = [];
            let nomor = 0;

            const tambahOpsi = (opsi, grup) => {
                if (opsi.disabled && opsi.value === '') return null;
                const teks = opsi.textContent.trim();
                if (kata && !normal(`${teks} ${grup ?? ''}`).includes(kata)) return null;
                const li = document.createElement('li');
                li.id = `${id}-opsi-${nomor++}`;
                li.setAttribute('role', 'option');
                li.dataset.indeks = opsi.index;
                const dipilih = opsi.selected && !(opsi.value === '' && opsi.disabled);
                li.setAttribute('aria-selected', dipilih ? 'true' : 'false');
                if (opsi.disabled) li.setAttribute('aria-disabled', 'true');
                li.className = `flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 aria-disabled:cursor-not-allowed aria-disabled:opacity-50 data-[aktif]:bg-muted ${grup ? 'pl-4' : ''}`;
                li.innerHTML = `<span class="min-w-0 flex-1"></span>${dipilih ? IKON_CENTANG : '<span class="size-4 shrink-0"></span>'}`;
                li.firstElementChild.textContent = teks;
                if (!opsi.disabled) pilihanTampil.push(li);
                return li;
            };

            for (const anak of select.children) {
                if (anak.tagName === 'OPTGROUP') {
                    const isi = [...anak.children].map((o) => tambahOpsi(o, anak.label)).filter(Boolean);
                    if (!isi.length) continue;
                    const judul = document.createElement('li');
                    judul.setAttribute('role', 'presentation');
                    judul.className = 'px-2 pt-2 pb-1 text-xs font-medium text-muted-foreground';
                    judul.textContent = anak.label;
                    daftar.append(judul, ...isi);
                } else {
                    const li = tambahOpsi(anak, null);
                    if (li) daftar.append(li);
                }
            }

            kosong.classList.toggle('hidden', pilihanTampil.length > 0);
            const indeksTerpilih = pilihanTampil.findIndex((li) => li.getAttribute('aria-selected') === 'true');
            aturAktif(kata ? 0 : Math.max(indeksTerpilih, 0));
        };

        const aturAktif = (i) => {
            pilihanTampil[aktif]?.removeAttribute('data-aktif');
            aktif = pilihanTampil.length ? Math.min(Math.max(i, 0), pilihanTampil.length - 1) : -1;
            const li = pilihanTampil[aktif];
            if (li) {
                li.setAttribute('data-aktif', '');
                li.scrollIntoView({ block: 'nearest' });
                cari.setAttribute('aria-activedescendant', li.id);
            } else {
                cari.removeAttribute('aria-activedescendant');
            }
        };

        const posisikan = () => {
            const kotak = tombol.getBoundingClientRect();
            const lebar = Math.max(kotak.width, 224);
            const kiri = Math.min(kotak.left, window.innerWidth - lebar - 8);
            const ruangBawah = window.innerHeight - kotak.bottom;
            panel.style.width = `${lebar}px`;
            panel.style.left = `${Math.max(8, kiri)}px`;
            // Buka ke atas bila ruang di bawah sempit.
            if (ruangBawah < 320 && kotak.top > ruangBawah) {
                panel.style.top = '';
                panel.style.bottom = `${window.innerHeight - kotak.top + 4}px`;
            } else {
                panel.style.bottom = '';
                panel.style.top = `${kotak.bottom + 4}px`;
            }
        };

        const buka = (kataAwal = '') => {
            if (terbuka || select.disabled) return;
            terbuka = true;
            cari.value = kataAwal;
            bangunDaftar();
            panel.classList.remove('hidden');
            posisikan();
            tombol.setAttribute('aria-expanded', 'true');
            cari.focus();
        };

        const tutup = (fokusKembali = true) => {
            if (!terbuka) return;
            terbuka = false;
            panel.classList.add('hidden');
            tombol.setAttribute('aria-expanded', 'false');
            if (fokusKembali) tombol.focus();
        };

        const pilih = (li) => {
            if (!li || li.getAttribute('aria-disabled') === 'true') return;
            select.selectedIndex = Number(li.dataset.indeks);
            select.dispatchEvent(new Event('input', { bubbles: true }));
            select.dispatchEvent(new Event('change', { bubbles: true }));
            sinkron();
            tutup();
        };

        // ---------- Peristiwa ----------
        tombol.addEventListener('click', () => (terbuka ? tutup() : buka()));
        tombol.addEventListener('keydown', (e) => {
            if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(e.key)) {
                e.preventDefault();
                buka();
            } else if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                // Mulai mengetik langsung mencari.
                e.preventDefault();
                buka(e.key);
            }
        });

        cari.addEventListener('input', bangunDaftar);
        cari.addEventListener('keydown', (e) => {
            const langkah = { ArrowDown: 1, ArrowUp: -1, PageDown: 5, PageUp: -5 }[e.key];
            if (langkah) {
                e.preventDefault();
                aturAktif(aktif + langkah);
            } else if (e.key === 'Home' || e.key === 'End') {
                e.preventDefault();
                aturAktif(e.key === 'Home' ? 0 : pilihanTampil.length - 1);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                pilih(pilihanTampil[aktif]);
            } else if (e.key === 'Escape') {
                e.preventDefault();
                e.stopPropagation(); // jangan ikut menutup drawer/menu lain
                tutup();
            } else if (e.key === 'Tab') {
                tutup(false);
            }
        });

        // mousedown (bukan click) agar fokus tidak sempat lepas dari kolom cari sebelum opsi terpilih.
        daftar.addEventListener('mousedown', (e) => {
            e.preventDefault();
            pilih(e.target.closest('[role="option"]'));
        });
        daftar.addEventListener('mousemove', (e) => {
            const i = pilihanTampil.indexOf(e.target.closest('[role="option"]'));
            if (i !== -1 && i !== aktif) aturAktif(i);
        });

        const klikLuar = (e) => {
            if (terbuka && !panel.contains(e.target) && !tombol.contains(e.target)) tutup(false);
        };
        const gulir = (e) => {
            if (terbuka && !panel.contains(e.target)) posisikan();
        };
        document.addEventListener('pointerdown', klikLuar);
        window.addEventListener('scroll', gulir, true);
        window.addEventListener('resize', gulir);

        // Klik label atau fokus dari skrip lain diteruskan ke tombol.
        select.addEventListener('focus', () => tombol.focus());
        // Kolom wajib yang belum dipilih: ganti gelembung bawaan browser (tidak terlihat) dengan membuka daftar.
        select.addEventListener('invalid', (e) => {
            e.preventDefault();
            galatKlien = true;
            sinkron();
            const pertama = select.form?.querySelector(':invalid');
            if (pertama === select) buka();
        });
        select.addEventListener('change', () => {
            if (select.value) galatKlien = false;
            sinkron();
        });
        const reset = () => setTimeout(sinkron);
        select.form?.addEventListener('reset', reset);

        // Opsi atau atribut yang diubah skrip lain (mis. Alpine) ikut tercermin.
        const pengamat = new MutationObserver(() => {
            sinkron();
            if (terbuka) bangunDaftar();
        });
        pengamat.observe(select, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled', 'class', 'aria-invalid', 'aria-describedby', 'required'] });

        sinkron();

        // Nilai yang diubah lewat x-model (mis. tombol "atur ulang") tidak memicu event change; ikuti lewat efek Alpine.
        // x-model sudah terpasang lebih dulu karena urutan direktif Alpine menaruh model sebelum direktif buatan.
        if (select._x_model) {
            effect(() => {
                select._x_model.get();
                queueMicrotask(sinkron);
            });
        }

        cleanup(() => {
            pengamat.disconnect();
            document.removeEventListener('pointerdown', klikLuar);
            window.removeEventListener('scroll', gulir, true);
            window.removeEventListener('resize', gulir);
            select.form?.removeEventListener('reset', reset);
            panel.remove();
            tombol.remove();
            select.removeAttribute('tabindex');
            select.removeAttribute('aria-hidden');
            if (gayaAwal === null) select.removeAttribute('style');
            else select.setAttribute('style', gayaAwal);
        });
    });
}
