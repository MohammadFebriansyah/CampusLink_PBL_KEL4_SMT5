<?php

/**
 * Pesan validasi bahasa Indonesia, hanya aturan yang dipakai CampusLink saat ini.
 * Aturan lain otomatis memakai bahasa cadangan (en). Tambahkan di sini bila perlu.
 * Aktifkan dengan APP_LOCALE=id di .env.
 */
return [
    'between' => [
        'numeric' => ':Attribute harus di antara :min dan :max.',
        'string' => ':Attribute harus di antara :min dan :max karakter.',
    ],
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'integer' => ':Attribute harus berupa angka bulat.',
    'max' => [
        'numeric' => ':Attribute tidak boleh lebih dari :max.',
        'string' => ':Attribute tidak boleh lebih dari :max karakter.',
    ],
    'min' => [
        'numeric' => ':Attribute minimal :min.',
        'string' => ':Attribute minimal :min karakter.',
    ],
    'required' => ':Attribute wajib diisi.',
    'string' => ':Attribute harus berupa teks.',
    'unique' => ':Attribute sudah terdaftar.',
    'url' => ':Attribute harus berupa URL yang valid.',

    'attributes' => [
        'name' => 'nama',
        'email' => 'email',
        'password' => 'kata sandi',
        'peran' => 'peran',
        'nim' => 'NIM',
        'nidn' => 'NIDN',
        'kelas' => 'kelas',
        'jurusan' => 'jurusan',
        'program_studi' => 'program studi',
        'angkatan' => 'angkatan',
        'nama_organisasi' => 'nama organisasi',
        'kontak' => 'kontak',
    ],
];
