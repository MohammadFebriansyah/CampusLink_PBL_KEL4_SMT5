@extends('errors.layout')

@section('code', '419')
@section('title', 'Sesi berakhir')
@section('message', 'Sesi Anda sudah berakhir. Muat ulang halaman lalu coba lagi.')

@section('actions')
    {{-- Buka ulang dengan GET agar formulir tidak terkirim lagi dengan token lama. --}}
    <a href="{{ url()->current() }}" class="btn btn-outline">Muat ulang halaman</a>
@endsection
