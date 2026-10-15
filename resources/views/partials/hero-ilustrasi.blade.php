{{-- Ilustrasi mahasiswa (cadangan bila public/images/hero-mahasiswa.png belum ada). --}}
<svg viewBox="0 0 400 480" class="h-full w-auto" role="img" aria-label="Ilustrasi mahasiswa melambaikan tangan">
    <defs>
        <linearGradient id="hero-baju" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0" stop-color="#ffffff"/>
            <stop offset="1" stop-color="#dfe6f7"/>
        </linearGradient>
        <linearGradient id="hero-jaket" x1="0" x2="1" y1="0" y2="1">
            <stop offset="0" stop-color="#2c4fd1"/>
            <stop offset="1" stop-color="#16308f"/>
        </linearGradient>
        <linearGradient id="hero-buku" x1="0" x2="1">
            <stop offset="0" stop-color="#ffb938"/>
            <stop offset="1" stop-color="#ff9500"/>
        </linearGradient>
    </defs>

    {{-- Lengan yang melambai (berputar dari bahu) --}}
    <g class="origin-[292px_292px] animate-wave">
        <path d="M292 300 L338 214 L352 128" fill="none" stroke="url(#hero-jaket)" stroke-width="46" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M346 150 L352 128" stroke="#ffffff" stroke-width="48" stroke-linecap="round"/>
        <g fill="#c98b5e">
            <rect x="334" y="70" width="12" height="40" rx="6" transform="rotate(-12 340 90)"/>
            <rect x="347" y="62" width="12" height="44" rx="6"/>
            <rect x="361" y="66" width="12" height="42" rx="6" transform="rotate(10 367 86)"/>
            <rect x="372" y="84" width="11" height="34" rx="5.5" transform="rotate(28 377 100)"/>
            <ellipse cx="354" cy="110" rx="26" ry="24"/>
            <rect x="320" y="104" width="12" height="30" rx="6" transform="rotate(-48 326 118)"/>
        </g>
    </g>

    {{-- Badan: kemeja putih + jaket almamater --}}
    <path d="M70 480 C70 345 108 262 200 250 C292 262 330 345 330 480 Z" fill="url(#hero-jaket)"/>
    <path d="M160 254 C175 300 190 360 200 480 C210 360 225 300 240 254 C226 249 212 247 200 247 C188 247 174 249 160 254 Z" fill="url(#hero-baju)"/>
    <path d="M176 246 L200 290 L224 246 L214 240 L200 262 L186 240 Z" fill="#ffffff" stroke="#cfd8ee" stroke-width="2"/>
    <path d="M160 254 L200 330 M240 254 L200 330" stroke="#0f2470" stroke-width="3" opacity="0.35"/>
    <circle cx="200" cy="340" r="4" fill="#cfd8ee"/>
    <circle cx="200" cy="372" r="4" fill="#cfd8ee"/>

    {{-- Leher dan kepala --}}
    <rect x="180" y="196" width="40" height="56" rx="18" fill="#b97a4f"/>
    <ellipse cx="146" cy="152" rx="11" ry="16" fill="#c98b5e"/>
    <ellipse cx="254" cy="152" rx="11" ry="16" fill="#c98b5e"/>
    <ellipse cx="200" cy="148" rx="54" ry="64" fill="#c98b5e"/>
    <path d="M144 140 C138 84 170 66 206 68 C246 70 268 96 258 146 C252 120 240 106 222 102 C196 112 166 108 152 104 C146 116 145 128 144 140 Z" fill="#1f1a2e"/>
    <path d="M162 92 C182 72 232 70 248 96" fill="none" stroke="#3a3350" stroke-width="5" stroke-linecap="round" opacity="0.6"/>

    {{-- Wajah tersenyum --}}
    <path d="M170 150 Q180 140 190 150" fill="none" stroke="#2a1e18" stroke-width="5" stroke-linecap="round"/>
    <path d="M210 150 Q220 140 230 150" fill="none" stroke="#2a1e18" stroke-width="5" stroke-linecap="round"/>
    <path d="M168 132 L190 128 M210 128 L232 132" stroke="#1f1a2e" stroke-width="5" stroke-linecap="round"/>
    <path d="M178 176 Q200 200 222 176 Q200 186 178 176 Z" fill="#ffffff" stroke="#7a3d22" stroke-width="3" stroke-linejoin="round"/>
    <ellipse cx="166" cy="170" rx="9" ry="5" fill="#e0796b" opacity="0.35"/>
    <ellipse cx="234" cy="170" rx="9" ry="5" fill="#e0796b" opacity="0.35"/>

    {{-- Lengan kiri memeluk buku --}}
    <rect x="118" y="330" width="120" height="86" rx="10" fill="url(#hero-buku)" transform="rotate(-8 178 373)"/>
    <rect x="128" y="340" width="100" height="10" rx="5" fill="#ffffff" opacity="0.5" transform="rotate(-8 178 373)"/>
    <path d="M112 300 C96 350 112 392 172 392" fill="none" stroke="url(#hero-jaket)" stroke-width="44" stroke-linecap="round"/>
    <ellipse cx="190" cy="388" rx="24" ry="18" fill="#c98b5e"/>
</svg>
