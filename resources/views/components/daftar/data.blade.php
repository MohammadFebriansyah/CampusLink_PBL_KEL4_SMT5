{{--
    Data awal daftar (hasil App\Support\Daftar) untuk Alpine.data('daftar'). Dibaca lewat JSON.parse, tidak dijalankan.
    JSON_HEX_TAG mengubah "<" menjadi <, jadi isi data tidak bisa menutup tag <script> ini.
    Props: id, nilai
--}}
@props(['id', 'nilai'])

<script type="application/json" id="{{ $id }}">{!! json_encode($nilai, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) !!}</script>
