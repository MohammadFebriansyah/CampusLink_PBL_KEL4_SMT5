-- =====================================================================
-- CampusLink — isi database Supabase (struktur + data contoh)
-- Dibuat dari 16 migration + seeder proyek (KeahlianSeeder, PenggunaSeeder,
-- InformasiSeeder). Hasilnya sama dengan `php artisan migrate --seed`.
--
-- Cara pakai: Supabase Dashboard -> SQL Editor -> New query ->
-- tempel seluruh isi file ini -> Run.
--
-- Semua akun contoh berpassword: password
-- PERINGATAN: baris DROP di bawah menghapus tabel CampusLink bila sudah ada,
-- supaya file ini aman dijalankan ulang. Jangan jalankan di database produksi
-- yang sudah berisi data asli.
-- =====================================================================

BEGIN;

DROP TABLE IF EXISTS public.bimbingan, public.pendaftaran, public.pengguna_keahlian,
  public.beasiswa, public.lomba, public.magang, public.mitra, public.dosen,
  public.mahasiswa, public.keahlian, public.sessions, public.password_reset_tokens,
  public.users, public.cache, public.cache_locks, public.jobs, public.job_batches,
  public.failed_jobs, public.migrations CASCADE;



SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

CREATE TABLE public.beasiswa (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    judul character varying(255) NOT NULL,
    penyelenggara character varying(255) NOT NULL,
    deskripsi text NOT NULL,
    persyaratan text NOT NULL,
    batas_pendaftaran date NOT NULL,
    kontak character varying(255) NOT NULL,
    tautan_pendaftaran character varying(500),
    status character varying(255) DEFAULT 'tayang'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    cara_daftar character varying(20) DEFAULT 'eksternal'::character varying NOT NULL,
    CONSTRAINT beasiswa_status_check CHECK (((status)::text = ANY ((ARRAY['tayang'::character varying, 'disembunyikan'::character varying])::text[])))
);

CREATE SEQUENCE public.beasiswa_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.beasiswa_id_seq OWNED BY public.beasiswa.id;

CREATE TABLE public.bimbingan (
    id bigint NOT NULL,
    mahasiswa_id bigint NOT NULL,
    dosen_id bigint NOT NULL,
    informasi_type character varying(255) NOT NULL,
    informasi_id bigint NOT NULL,
    pesan text,
    status character varying(20) DEFAULT 'menunggu'::character varying NOT NULL,
    catatan text,
    ditinjau_pada timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    bukti character varying(255)
);

CREATE SEQUENCE public.bimbingan_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.bimbingan_id_seq OWNED BY public.bimbingan.id;

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);

CREATE TABLE public.dosen (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    nidn character varying(20) NOT NULL,
    jurusan character varying(255) NOT NULL,
    program_studi character varying(255) NOT NULL,
    foto character varying(255),
    kontak character varying(255),
    tentang text,
    bersedia_membimbing_magang boolean DEFAULT false NOT NULL,
    bersedia_membimbing_lomba boolean DEFAULT false NOT NULL,
    tampil_di_direktori boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    disembunyikan_admin boolean DEFAULT false NOT NULL
);

CREATE SEQUENCE public.dosen_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.dosen_id_seq OWNED BY public.dosen.id;

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection character varying(255) NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;

CREATE TABLE public.keahlian (
    id bigint NOT NULL,
    nama character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);

CREATE SEQUENCE public.keahlian_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.keahlian_id_seq OWNED BY public.keahlian.id;

CREATE TABLE public.lomba (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    judul character varying(255) NOT NULL,
    penyelenggara character varying(255) NOT NULL,
    deskripsi text NOT NULL,
    persyaratan text,
    tingkat character varying(255) NOT NULL,
    tanggal_mulai date NOT NULL,
    tanggal_selesai date NOT NULL,
    batas_pendaftaran date NOT NULL,
    kontak character varying(255) NOT NULL,
    tautan_pendaftaran character varying(500),
    status character varying(255) DEFAULT 'tayang'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    cara_daftar character varying(20) DEFAULT 'eksternal'::character varying NOT NULL,
    CONSTRAINT lomba_status_check CHECK (((status)::text = ANY ((ARRAY['tayang'::character varying, 'disembunyikan'::character varying])::text[]))),
    CONSTRAINT lomba_tingkat_check CHECK (((tingkat)::text = ANY ((ARRAY['kampus'::character varying, 'regional'::character varying, 'nasional'::character varying, 'internasional'::character varying])::text[])))
);

CREATE SEQUENCE public.lomba_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.lomba_id_seq OWNED BY public.lomba.id;

CREATE TABLE public.magang (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    judul character varying(255) NOT NULL,
    penyelenggara character varying(255) NOT NULL,
    deskripsi text NOT NULL,
    persyaratan text NOT NULL,
    lokasi character varying(255),
    periode_mulai date NOT NULL,
    periode_selesai date NOT NULL,
    batas_pendaftaran date NOT NULL,
    kontak character varying(255) NOT NULL,
    tautan_pendaftaran character varying(500),
    status character varying(255) DEFAULT 'tayang'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    cara_daftar character varying(20) DEFAULT 'eksternal'::character varying NOT NULL,
    CONSTRAINT magang_status_check CHECK (((status)::text = ANY ((ARRAY['tayang'::character varying, 'disembunyikan'::character varying])::text[])))
);

CREATE SEQUENCE public.magang_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.magang_id_seq OWNED BY public.magang.id;

CREATE TABLE public.mahasiswa (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    nim character varying(20) NOT NULL,
    kelas character varying(20) NOT NULL,
    jurusan character varying(255) NOT NULL,
    program_studi character varying(255) NOT NULL,
    angkatan smallint NOT NULL,
    foto character varying(255),
    kontak character varying(255),
    tentang text,
    sertifikasi text,
    pelatihan text,
    proyek text,
    pencapaian text,
    tampil_di_direktori boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    disembunyikan_admin boolean DEFAULT false NOT NULL
);

CREATE SEQUENCE public.mahasiswa_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.mahasiswa_id_seq OWNED BY public.mahasiswa.id;

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;

CREATE TABLE public.mitra (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    nama_organisasi character varying(255) NOT NULL,
    deskripsi text,
    kontak character varying(255) NOT NULL,
    website character varying(255),
    status_verifikasi character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT mitra_status_verifikasi_check CHECK (((status_verifikasi)::text = ANY ((ARRAY['pending'::character varying, 'disetujui'::character varying, 'ditolak'::character varying])::text[])))
);

CREATE SEQUENCE public.mitra_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.mitra_id_seq OWNED BY public.mitra.id;

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);

CREATE TABLE public.pendaftaran (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    informasi_type character varying(255) NOT NULL,
    informasi_id bigint NOT NULL,
    cv character varying(255) NOT NULL,
    status character varying(20) DEFAULT 'menunggu'::character varying NOT NULL,
    catatan text,
    ditinjau_oleh bigint,
    ditinjau_pada timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);

CREATE SEQUENCE public.pendaftaran_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.pendaftaran_id_seq OWNED BY public.pendaftaran.id;

CREATE TABLE public.pengguna_keahlian (
    user_id bigint NOT NULL,
    keahlian_id bigint NOT NULL
);

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    password character varying(255),
    role character varying(255) DEFAULT 'mahasiswa'::character varying NOT NULL,
    status character varying(255) DEFAULT 'aktif'::character varying NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    google_id character varying(255),
    CONSTRAINT users_role_check CHECK (((role)::text = ANY ((ARRAY['admin'::character varying, 'mahasiswa'::character varying, 'dosen'::character varying, 'mitra'::character varying])::text[]))),
    CONSTRAINT users_status_check CHECK (((status)::text = ANY ((ARRAY['aktif'::character varying, 'nonaktif'::character varying])::text[])))
);

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;

ALTER TABLE ONLY public.beasiswa ALTER COLUMN id SET DEFAULT nextval('public.beasiswa_id_seq'::regclass);

ALTER TABLE ONLY public.bimbingan ALTER COLUMN id SET DEFAULT nextval('public.bimbingan_id_seq'::regclass);

ALTER TABLE ONLY public.dosen ALTER COLUMN id SET DEFAULT nextval('public.dosen_id_seq'::regclass);

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);

ALTER TABLE ONLY public.keahlian ALTER COLUMN id SET DEFAULT nextval('public.keahlian_id_seq'::regclass);

ALTER TABLE ONLY public.lomba ALTER COLUMN id SET DEFAULT nextval('public.lomba_id_seq'::regclass);

ALTER TABLE ONLY public.magang ALTER COLUMN id SET DEFAULT nextval('public.magang_id_seq'::regclass);

ALTER TABLE ONLY public.mahasiswa ALTER COLUMN id SET DEFAULT nextval('public.mahasiswa_id_seq'::regclass);

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);

ALTER TABLE ONLY public.mitra ALTER COLUMN id SET DEFAULT nextval('public.mitra_id_seq'::regclass);

ALTER TABLE ONLY public.pendaftaran ALTER COLUMN id SET DEFAULT nextval('public.pendaftaran_id_seq'::regclass);

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);

INSERT INTO public.beasiswa (id, user_id, judul, penyelenggara, deskripsi, persyaratan, batas_pendaftaran, kontak, tautan_pendaftaran, status, created_at, updated_at, cara_daftar) VALUES (1, 11, 'Beasiswa Talenta Digital', 'PT Nusantara Teknologi', 'Bantuan biaya pendidikan bagi mahasiswa berprestasi bidang teknologi.', 'IPK minimal 3,25.
Mahasiswa aktif semester 3 sampai 7.
Melampirkan surat rekomendasi dosen.', '2026-11-24', 'beasiswa@nusantaratech.example', 'https://nusantaratech.example/beasiswa', 'tayang', '2026-10-15 14:04:06', '2026-10-15 14:04:06', 'internal');
INSERT INTO public.beasiswa (id, user_id, judul, penyelenggara, deskripsi, persyaratan, batas_pendaftaran, kontak, tautan_pendaftaran, status, created_at, updated_at, cara_daftar) VALUES (2, 1, 'Beasiswa Peningkatan Prestasi Akademik', 'Lembaga Pendidikan Mandiri', 'Beasiswa untuk mahasiswa dengan prestasi akademik konsisten.', 'IPK minimal 3,5.
Tidak sedang menerima beasiswa lain.', '2026-11-12', 'info@lpm.example', 'https://lpm.example/beasiswa-prestasi', 'tayang', '2026-10-15 14:04:06', '2026-10-15 14:04:06', 'eksternal');

INSERT INTO public.dosen (id, user_id, nidn, jurusan, program_studi, foto, kontak, tentang, bersedia_membimbing_magang, bersedia_membimbing_lomba, tampil_di_direktori, created_at, updated_at, disembunyikan_admin) VALUES (1, 8, '0701019001', 'Teknologi Informasi', 'Sistem Informasi Bisnis', NULL, '081300000200', 'Dosen Sistem Informasi Bisnis, bidang Basis Data dan Manajemen Proyek.', true, true, true, '2026-10-15 14:04:06', '2026-10-15 14:04:06', false);
INSERT INTO public.dosen (id, user_id, nidn, jurusan, program_studi, foto, kontak, tentang, bersedia_membimbing_magang, bersedia_membimbing_lomba, tampil_di_direktori, created_at, updated_at, disembunyikan_admin) VALUES (2, 9, '0702029002', 'Teknologi Informasi', 'Teknik Informatika', NULL, '081300000201', 'Dosen Teknik Informatika, bidang Web Development dan Keamanan Siber.', true, false, true, '2026-10-15 14:04:06', '2026-10-15 14:04:06', false);
INSERT INTO public.dosen (id, user_id, nidn, jurusan, program_studi, foto, kontak, tentang, bersedia_membimbing_magang, bersedia_membimbing_lomba, tampil_di_direktori, created_at, updated_at, disembunyikan_admin) VALUES (3, 10, '0703039003', 'Administrasi Niaga', 'Administrasi Bisnis', NULL, '081300000202', 'Dosen Administrasi Bisnis, bidang Kewirausahaan dan Public Speaking.', false, true, true, '2026-10-15 14:04:06', '2026-10-15 14:04:06', false);

INSERT INTO public.keahlian (id, nama, created_at, updated_at) VALUES (1, 'Web Development', '2026-10-15 14:04:05', '2026-10-15 14:04:05');
INSERT INTO public.keahlian (id, nama, created_at, updated_at) VALUES (2, 'Mobile Development', '2026-10-15 14:04:05', '2026-10-15 14:04:05');
INSERT INTO public.keahlian (id, nama, created_at, updated_at) VALUES (3, 'UI/UX Design', '2026-10-15 14:04:05', '2026-10-15 14:04:05');
INSERT INTO public.keahlian (id, nama, created_at, updated_at) VALUES (4, 'Basis Data', '2026-10-15 14:04:05', '2026-10-15 14:04:05');
INSERT INTO public.keahlian (id, nama, created_at, updated_at) VALUES (5, 'Analisis Data', '2026-10-15 14:04:05', '2026-10-15 14:04:05');
INSERT INTO public.keahlian (id, nama, created_at, updated_at) VALUES (6, 'Machine Learning', '2026-10-15 14:04:05', '2026-10-15 14:04:05');
INSERT INTO public.keahlian (id, nama, created_at, updated_at) VALUES (7, 'Jaringan Komputer', '2026-10-15 14:04:05', '2026-10-15 14:04:05');
INSERT INTO public.keahlian (id, nama, created_at, updated_at) VALUES (8, 'Keamanan Siber', '2026-10-15 14:04:05', '2026-10-15 14:04:05');
INSERT INTO public.keahlian (id, nama, created_at, updated_at) VALUES (9, 'Manajemen Proyek', '2026-10-15 14:04:05', '2026-10-15 14:04:05');
INSERT INTO public.keahlian (id, nama, created_at, updated_at) VALUES (10, 'Analisis Bisnis', '2026-10-15 14:04:05', '2026-10-15 14:04:05');
INSERT INTO public.keahlian (id, nama, created_at, updated_at) VALUES (11, 'Desain Grafis', '2026-10-15 14:04:05', '2026-10-15 14:04:05');
INSERT INTO public.keahlian (id, nama, created_at, updated_at) VALUES (12, 'Penulisan Karya Ilmiah', '2026-10-15 14:04:05', '2026-10-15 14:04:05');
INSERT INTO public.keahlian (id, nama, created_at, updated_at) VALUES (13, 'Public Speaking', '2026-10-15 14:04:05', '2026-10-15 14:04:05');
INSERT INTO public.keahlian (id, nama, created_at, updated_at) VALUES (14, 'Kewirausahaan', '2026-10-15 14:04:05', '2026-10-15 14:04:05');

INSERT INTO public.lomba (id, user_id, judul, penyelenggara, deskripsi, persyaratan, tingkat, tanggal_mulai, tanggal_selesai, batas_pendaftaran, kontak, tautan_pendaftaran, status, created_at, updated_at, cara_daftar) VALUES (1, 11, 'Hackathon Inovasi Kampus', 'PT Nusantara Teknologi', 'Kompetisi 24 jam membangun prototipe solusi untuk masalah kampus.', 'Tim 3 sampai 5 mahasiswa aktif.', 'nasional', '2026-12-14', '2026-12-15', '2026-11-24', 'hackathon@nusantaratech.example', 'https://nusantaratech.example/hackathon', 'tayang', '2026-10-15 14:04:06', '2026-10-15 14:04:06', 'internal');
INSERT INTO public.lomba (id, user_id, judul, penyelenggara, deskripsi, persyaratan, tingkat, tanggal_mulai, tanggal_selesai, batas_pendaftaran, kontak, tautan_pendaftaran, status, created_at, updated_at, cara_daftar) VALUES (2, 1, 'Lomba Desain Antarmuka Mahasiswa', 'Komunitas Desain Digital', 'Merancang antarmuka aplikasi dengan tema layanan publik.', 'Individu atau tim maksimal 2 orang.', 'regional', '2026-12-04', '2026-12-06', '2026-11-19', 'panitia@desaindigital.example', 'https://desaindigital.example/lomba-ui', 'tayang', '2026-10-15 14:04:06', '2026-10-15 14:04:06', 'eksternal');
INSERT INTO public.lomba (id, user_id, judul, penyelenggara, deskripsi, persyaratan, tingkat, tanggal_mulai, tanggal_selesai, batas_pendaftaran, kontak, tautan_pendaftaran, status, created_at, updated_at, cara_daftar) VALUES (3, 1, 'Kompetisi Rencana Bisnis Mahasiswa', 'Pusat Kewirausahaan Kampus', 'Pemaparan rencana bisnis di depan juri praktisi.', NULL, 'kampus', '2026-11-09', '2026-11-09', '2026-10-29', 'kewirausahaan@kampus.example', 'https://kampus.example/rencana-bisnis', 'tayang', '2026-10-15 14:04:06', '2026-10-15 14:04:06', 'internal');

INSERT INTO public.magang (id, user_id, judul, penyelenggara, deskripsi, persyaratan, lokasi, periode_mulai, periode_selesai, batas_pendaftaran, kontak, tautan_pendaftaran, status, created_at, updated_at, cara_daftar) VALUES (1, 11, 'Magang Web Developer Laravel', 'PT Nusantara Teknologi', 'Terlibat dalam pengembangan aplikasi web internal bersama tim engineering.', 'Mahasiswa semester 5 ke atas.
Menguasai dasar PHP dan SQL.
Memahami konsep MVC.', 'Malang (hibrid)', '2026-12-01', '2027-03-01', '2026-11-14', 'hrd@nusantaratech.example', 'https://nusantaratech.example/magang/web', 'tayang', '2026-10-15 14:04:06', '2026-10-15 14:04:06', 'internal');
INSERT INTO public.magang (id, user_id, judul, penyelenggara, deskripsi, persyaratan, lokasi, periode_mulai, periode_selesai, batas_pendaftaran, kontak, tautan_pendaftaran, status, created_at, updated_at, cara_daftar) VALUES (2, 11, 'Magang UI/UX Designer', 'PT Nusantara Teknologi', 'Membantu merancang antarmuka dan melakukan uji kegunaan produk.', 'Memiliki portofolio desain.
Terbiasa memakai Figma.', 'Remote', '2026-12-01', '2027-02-01', '2026-11-05', 'hrd@nusantaratech.example', 'https://nusantaratech.example/magang/uiux', 'tayang', '2026-10-15 14:04:06', '2026-10-15 14:04:06', 'internal');
INSERT INTO public.magang (id, user_id, judul, penyelenggara, deskripsi, persyaratan, lokasi, periode_mulai, periode_selesai, batas_pendaftaran, kontak, tautan_pendaftaran, status, created_at, updated_at, cara_daftar) VALUES (3, 1, 'Magang Analis Data', 'PT Data Wawasan Indonesia', 'Mengolah dan memvisualisasikan data penjualan untuk laporan bulanan.', 'Memahami SQL dasar.
Bisa memakai Excel atau Python untuk analisis.', 'Surabaya', '2027-01-01', '2027-04-01', '2026-11-29', 'karir@datawawasan.example', 'https://datawawasan.example/karir/analis-data', 'tayang', '2026-10-15 14:04:06', '2026-10-15 14:04:06', 'eksternal');

INSERT INTO public.mahasiswa (id, user_id, nim, kelas, jurusan, program_studi, angkatan, foto, kontak, tentang, sertifikasi, pelatihan, proyek, pencapaian, tampil_di_direktori, created_at, updated_at, disembunyikan_admin) VALUES (1, 2, '2441079001', 'SIB-3A', 'Teknologi Informasi', 'Sistem Informasi Bisnis', 2024, NULL, '081200000100', 'Mahasiswa Sistem Informasi Bisnis angkatan 2024, tertarik pada Web Development dan UI/UX Design.', NULL, NULL, NULL, NULL, true, '2026-10-15 14:04:06', '2026-10-15 14:04:06', false);
INSERT INTO public.mahasiswa (id, user_id, nim, kelas, jurusan, program_studi, angkatan, foto, kontak, tentang, sertifikasi, pelatihan, proyek, pencapaian, tampil_di_direktori, created_at, updated_at, disembunyikan_admin) VALUES (2, 3, '2441079002', 'SIB-3B', 'Teknologi Informasi', 'Sistem Informasi Bisnis', 2024, NULL, '081200000101', 'Mahasiswa Sistem Informasi Bisnis angkatan 2024, tertarik pada Basis Data dan Analisis Data.', NULL, NULL, NULL, NULL, true, '2026-10-15 14:04:06', '2026-10-15 14:04:06', false);
INSERT INTO public.mahasiswa (id, user_id, nim, kelas, jurusan, program_studi, angkatan, foto, kontak, tentang, sertifikasi, pelatihan, proyek, pencapaian, tampil_di_direktori, created_at, updated_at, disembunyikan_admin) VALUES (3, 4, '2341079003', 'TI-5A', 'Teknologi Informasi', 'Teknik Informatika', 2023, NULL, '081200000102', 'Mahasiswa Teknik Informatika angkatan 2023, tertarik pada Mobile Development dan Machine Learning.', NULL, NULL, NULL, NULL, true, '2026-10-15 14:04:06', '2026-10-15 14:04:06', false);
INSERT INTO public.mahasiswa (id, user_id, nim, kelas, jurusan, program_studi, angkatan, foto, kontak, tentang, sertifikasi, pelatihan, proyek, pencapaian, tampil_di_direktori, created_at, updated_at, disembunyikan_admin) VALUES (4, 5, '2341079004', 'TI-5B', 'Teknologi Informasi', 'Teknik Informatika', 2023, NULL, '081200000103', 'Mahasiswa Teknik Informatika angkatan 2023, tertarik pada Jaringan Komputer dan Keamanan Siber.', NULL, NULL, NULL, NULL, true, '2026-10-15 14:04:06', '2026-10-15 14:04:06', false);
INSERT INTO public.mahasiswa (id, user_id, nim, kelas, jurusan, program_studi, angkatan, foto, kontak, tentang, sertifikasi, pelatihan, proyek, pencapaian, tampil_di_direktori, created_at, updated_at, disembunyikan_admin) VALUES (5, 6, '2541079005', 'AK-1A', 'Akuntansi', 'Akuntansi Manajemen', 2025, NULL, '081200000104', 'Mahasiswa Akuntansi Manajemen angkatan 2025, tertarik pada Analisis Bisnis dan Kewirausahaan.', NULL, NULL, NULL, NULL, true, '2026-10-15 14:04:06', '2026-10-15 14:04:06', false);
INSERT INTO public.mahasiswa (id, user_id, nim, kelas, jurusan, program_studi, angkatan, foto, kontak, tentang, sertifikasi, pelatihan, proyek, pencapaian, tampil_di_direktori, created_at, updated_at, disembunyikan_admin) VALUES (6, 7, '2241079006', 'AN-7A', 'Administrasi Niaga', 'Administrasi Bisnis', 2022, NULL, '081200000105', 'Mahasiswa Administrasi Bisnis angkatan 2022, tertarik pada Public Speaking dan Manajemen Proyek.', NULL, NULL, NULL, NULL, true, '2026-10-15 14:04:06', '2026-10-15 14:04:06', false);

INSERT INTO public.migrations (id, migration, batch) VALUES (1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (3, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (4, '2026_10_11_000001_create_keahlian_table', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (5, '2026_10_11_000002_create_mahasiswa_table', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (6, '2026_10_11_000003_create_dosen_table', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (7, '2026_10_11_000004_create_mitra_table', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (8, '2026_10_11_000005_create_magang_table', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (9, '2026_10_11_000006_create_lomba_table', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (10, '2026_10_11_000007_create_beasiswa_table', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (11, '2026_10_11_000008_enable_rls_on_public_tables', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (12, '2026_10_11_000009_add_google_to_users_table', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (13, '2026_10_11_000010_add_disembunyikan_admin_to_profil_tables', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (14, '2026_10_15_000011_add_cara_daftar_to_informasi_tables', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (15, '2026_10_15_093418_create_pendaftaran_dan_bimbingan_tables', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (16, '2026_10_15_100301_add_bukti_to_bimbingan_table', 1);

INSERT INTO public.mitra (id, user_id, nama_organisasi, deskripsi, kontak, website, status_verifikasi, created_at, updated_at) VALUES (1, 11, 'PT Nusantara Teknologi', 'Perusahaan pengembang perangkat lunak.', '0341-000001', 'https://nusantaratech.example', 'disetujui', '2026-10-15 14:04:06', '2026-10-15 14:04:06');
INSERT INTO public.mitra (id, user_id, nama_organisasi, deskripsi, kontak, website, status_verifikasi, created_at, updated_at) VALUES (2, 12, 'Yayasan Kreasi Muda', 'Yayasan pendukung talenta muda.', '0341-000002', NULL, 'pending', '2026-10-15 14:04:06', '2026-10-15 14:04:06');

INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (2, 1);
INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (2, 3);
INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (3, 4);
INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (3, 5);
INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (4, 2);
INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (4, 6);
INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (5, 7);
INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (5, 8);
INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (6, 10);
INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (6, 14);
INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (7, 9);
INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (7, 13);
INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (8, 4);
INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (8, 9);
INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (9, 1);
INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (9, 8);
INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (10, 13);
INSERT INTO public.pengguna_keahlian (user_id, keahlian_id) VALUES (10, 14);

INSERT INTO public.users (id, name, email, password, role, status, remember_token, created_at, updated_at, google_id) VALUES (1, 'Admin CampusLink', 'admin@campuslink.test', '$2y$12$vfyOFHfDq7yCRGRPwW9rY.G3BSwBEFpR2Fman1fvoBAhZBWCTe.1W', 'admin', 'aktif', NULL, '2026-10-15 14:04:06', '2026-10-15 14:04:06', NULL);
INSERT INTO public.users (id, name, email, password, role, status, remember_token, created_at, updated_at, google_id) VALUES (2, 'Alya Putri Ramadhani', 'alya.putri@polinema.ac.id', '$2y$12$vfyOFHfDq7yCRGRPwW9rY.G3BSwBEFpR2Fman1fvoBAhZBWCTe.1W', 'mahasiswa', 'aktif', NULL, '2026-10-15 14:04:06', '2026-10-15 14:04:06', NULL);
INSERT INTO public.users (id, name, email, password, role, status, remember_token, created_at, updated_at, google_id) VALUES (3, 'Bima Aditya Pratama', 'bima.aditya@polinema.ac.id', '$2y$12$vfyOFHfDq7yCRGRPwW9rY.G3BSwBEFpR2Fman1fvoBAhZBWCTe.1W', 'mahasiswa', 'aktif', NULL, '2026-10-15 14:04:06', '2026-10-15 14:04:06', NULL);
INSERT INTO public.users (id, name, email, password, role, status, remember_token, created_at, updated_at, google_id) VALUES (4, 'Citra Maharani', 'citra.maharani@polinema.ac.id', '$2y$12$vfyOFHfDq7yCRGRPwW9rY.G3BSwBEFpR2Fman1fvoBAhZBWCTe.1W', 'mahasiswa', 'aktif', NULL, '2026-10-15 14:04:06', '2026-10-15 14:04:06', NULL);
INSERT INTO public.users (id, name, email, password, role, status, remember_token, created_at, updated_at, google_id) VALUES (5, 'Dimas Raka Saputra', 'dimas.raka@polinema.ac.id', '$2y$12$vfyOFHfDq7yCRGRPwW9rY.G3BSwBEFpR2Fman1fvoBAhZBWCTe.1W', 'mahasiswa', 'aktif', NULL, '2026-10-15 14:04:06', '2026-10-15 14:04:06', NULL);
INSERT INTO public.users (id, name, email, password, role, status, remember_token, created_at, updated_at, google_id) VALUES (6, 'Eka Nur Fadilah', 'eka.nur@polinema.ac.id', '$2y$12$vfyOFHfDq7yCRGRPwW9rY.G3BSwBEFpR2Fman1fvoBAhZBWCTe.1W', 'mahasiswa', 'aktif', NULL, '2026-10-15 14:04:06', '2026-10-15 14:04:06', NULL);
INSERT INTO public.users (id, name, email, password, role, status, remember_token, created_at, updated_at, google_id) VALUES (7, 'Farhan Maulana', 'farhan.maulana@polinema.ac.id', '$2y$12$vfyOFHfDq7yCRGRPwW9rY.G3BSwBEFpR2Fman1fvoBAhZBWCTe.1W', 'mahasiswa', 'aktif', NULL, '2026-10-15 14:04:06', '2026-10-15 14:04:06', NULL);
INSERT INTO public.users (id, name, email, password, role, status, remember_token, created_at, updated_at, google_id) VALUES (8, 'Dr. Rina Kusumawati, S.Kom., M.T.', 'rina.kusumawati@polinema.ac.id', '$2y$12$vfyOFHfDq7yCRGRPwW9rY.G3BSwBEFpR2Fman1fvoBAhZBWCTe.1W', 'dosen', 'aktif', NULL, '2026-10-15 14:04:06', '2026-10-15 14:04:06', NULL);
INSERT INTO public.users (id, name, email, password, role, status, remember_token, created_at, updated_at, google_id) VALUES (9, 'Hendra Wijaya, S.T., M.Kom.', 'hendra.wijaya@polinema.ac.id', '$2y$12$vfyOFHfDq7yCRGRPwW9rY.G3BSwBEFpR2Fman1fvoBAhZBWCTe.1W', 'dosen', 'aktif', NULL, '2026-10-15 14:04:06', '2026-10-15 14:04:06', NULL);
INSERT INTO public.users (id, name, email, password, role, status, remember_token, created_at, updated_at, google_id) VALUES (10, 'Sari Lestari, S.E., M.M.', 'sari.lestari@polinema.ac.id', '$2y$12$vfyOFHfDq7yCRGRPwW9rY.G3BSwBEFpR2Fman1fvoBAhZBWCTe.1W', 'dosen', 'aktif', NULL, '2026-10-15 14:04:06', '2026-10-15 14:04:06', NULL);
INSERT INTO public.users (id, name, email, password, role, status, remember_token, created_at, updated_at, google_id) VALUES (11, 'PT Nusantara Teknologi', 'hrd@nusantaratech.example', '$2y$12$vfyOFHfDq7yCRGRPwW9rY.G3BSwBEFpR2Fman1fvoBAhZBWCTe.1W', 'mitra', 'aktif', NULL, '2026-10-15 14:04:06', '2026-10-15 14:04:06', NULL);
INSERT INTO public.users (id, name, email, password, role, status, remember_token, created_at, updated_at, google_id) VALUES (12, 'Yayasan Kreasi Muda', 'info@kreasimuda.example', '$2y$12$vfyOFHfDq7yCRGRPwW9rY.G3BSwBEFpR2Fman1fvoBAhZBWCTe.1W', 'mitra', 'aktif', NULL, '2026-10-15 14:04:06', '2026-10-15 14:04:06', NULL);

SELECT pg_catalog.setval('public.beasiswa_id_seq', 2, true);

SELECT pg_catalog.setval('public.bimbingan_id_seq', 1, false);

SELECT pg_catalog.setval('public.dosen_id_seq', 3, true);

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);

SELECT pg_catalog.setval('public.keahlian_id_seq', 14, true);

SELECT pg_catalog.setval('public.lomba_id_seq', 3, true);

SELECT pg_catalog.setval('public.magang_id_seq', 3, true);

SELECT pg_catalog.setval('public.mahasiswa_id_seq', 6, true);

SELECT pg_catalog.setval('public.migrations_id_seq', 16, true);

SELECT pg_catalog.setval('public.mitra_id_seq', 2, true);

SELECT pg_catalog.setval('public.pendaftaran_id_seq', 1, false);

SELECT pg_catalog.setval('public.users_id_seq', 12, true);

ALTER TABLE ONLY public.beasiswa
    ADD CONSTRAINT beasiswa_pkey PRIMARY KEY (id);

ALTER TABLE ONLY public.bimbingan
    ADD CONSTRAINT bimbingan_mahasiswa_id_informasi_type_informasi_id_unique UNIQUE (mahasiswa_id, informasi_type, informasi_id);

ALTER TABLE ONLY public.bimbingan
    ADD CONSTRAINT bimbingan_pkey PRIMARY KEY (id);

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);

ALTER TABLE ONLY public.dosen
    ADD CONSTRAINT dosen_nidn_unique UNIQUE (nidn);

ALTER TABLE ONLY public.dosen
    ADD CONSTRAINT dosen_pkey PRIMARY KEY (id);

ALTER TABLE ONLY public.dosen
    ADD CONSTRAINT dosen_user_id_unique UNIQUE (user_id);

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);

ALTER TABLE ONLY public.keahlian
    ADD CONSTRAINT keahlian_nama_unique UNIQUE (nama);

ALTER TABLE ONLY public.keahlian
    ADD CONSTRAINT keahlian_pkey PRIMARY KEY (id);

ALTER TABLE ONLY public.lomba
    ADD CONSTRAINT lomba_pkey PRIMARY KEY (id);

ALTER TABLE ONLY public.magang
    ADD CONSTRAINT magang_pkey PRIMARY KEY (id);

ALTER TABLE ONLY public.mahasiswa
    ADD CONSTRAINT mahasiswa_nim_unique UNIQUE (nim);

ALTER TABLE ONLY public.mahasiswa
    ADD CONSTRAINT mahasiswa_pkey PRIMARY KEY (id);

ALTER TABLE ONLY public.mahasiswa
    ADD CONSTRAINT mahasiswa_user_id_unique UNIQUE (user_id);

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);

ALTER TABLE ONLY public.mitra
    ADD CONSTRAINT mitra_pkey PRIMARY KEY (id);

ALTER TABLE ONLY public.mitra
    ADD CONSTRAINT mitra_user_id_unique UNIQUE (user_id);

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);

ALTER TABLE ONLY public.pendaftaran
    ADD CONSTRAINT pendaftaran_pkey PRIMARY KEY (id);

ALTER TABLE ONLY public.pendaftaran
    ADD CONSTRAINT pendaftaran_user_id_informasi_type_informasi_id_unique UNIQUE (user_id, informasi_type, informasi_id);

ALTER TABLE ONLY public.pengguna_keahlian
    ADD CONSTRAINT pengguna_keahlian_pkey PRIMARY KEY (user_id, keahlian_id);

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_google_id_unique UNIQUE (google_id);

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);

CREATE INDEX beasiswa_batas_pendaftaran_index ON public.beasiswa USING btree (batas_pendaftaran);

CREATE INDEX bimbingan_dosen_id_status_index ON public.bimbingan USING btree (dosen_id, status);

CREATE INDEX bimbingan_informasi_type_informasi_id_index ON public.bimbingan USING btree (informasi_type, informasi_id);

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);

CREATE INDEX dosen_program_studi_index ON public.dosen USING btree (program_studi);

CREATE INDEX failed_jobs_connection_queue_failed_at_index ON public.failed_jobs USING btree (connection, queue, failed_at);

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);

CREATE INDEX lomba_batas_pendaftaran_index ON public.lomba USING btree (batas_pendaftaran);

CREATE INDEX magang_batas_pendaftaran_index ON public.magang USING btree (batas_pendaftaran);

CREATE INDEX mahasiswa_angkatan_index ON public.mahasiswa USING btree (angkatan);

CREATE INDEX mahasiswa_program_studi_index ON public.mahasiswa USING btree (program_studi);

CREATE INDEX pendaftaran_informasi_type_informasi_id_index ON public.pendaftaran USING btree (informasi_type, informasi_id);

CREATE INDEX pendaftaran_status_index ON public.pendaftaran USING btree (status);

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);

ALTER TABLE ONLY public.beasiswa
    ADD CONSTRAINT beasiswa_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;

ALTER TABLE ONLY public.bimbingan
    ADD CONSTRAINT bimbingan_dosen_id_foreign FOREIGN KEY (dosen_id) REFERENCES public.users(id) ON DELETE CASCADE;

ALTER TABLE ONLY public.bimbingan
    ADD CONSTRAINT bimbingan_mahasiswa_id_foreign FOREIGN KEY (mahasiswa_id) REFERENCES public.users(id) ON DELETE CASCADE;

ALTER TABLE ONLY public.dosen
    ADD CONSTRAINT dosen_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;

ALTER TABLE ONLY public.lomba
    ADD CONSTRAINT lomba_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;

ALTER TABLE ONLY public.magang
    ADD CONSTRAINT magang_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;

ALTER TABLE ONLY public.mahasiswa
    ADD CONSTRAINT mahasiswa_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;

ALTER TABLE ONLY public.mitra
    ADD CONSTRAINT mitra_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;

ALTER TABLE ONLY public.pendaftaran
    ADD CONSTRAINT pendaftaran_ditinjau_oleh_foreign FOREIGN KEY (ditinjau_oleh) REFERENCES public.users(id) ON DELETE SET NULL;

ALTER TABLE ONLY public.pendaftaran
    ADD CONSTRAINT pendaftaran_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;

ALTER TABLE ONLY public.pengguna_keahlian
    ADD CONSTRAINT pengguna_keahlian_keahlian_id_foreign FOREIGN KEY (keahlian_id) REFERENCES public.keahlian(id) ON DELETE CASCADE;

ALTER TABLE ONLY public.pengguna_keahlian
    ADD CONSTRAINT pengguna_keahlian_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;

ALTER TABLE public.beasiswa ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.bimbingan ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.cache ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.cache_locks ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.dosen ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.failed_jobs ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.job_batches ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.jobs ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.keahlian ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.lomba ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.magang ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.mahasiswa ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.migrations ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.mitra ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.password_reset_tokens ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.pendaftaran ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.pengguna_keahlian ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.sessions ENABLE ROW LEVEL SECURITY;

ALTER TABLE public.users ENABLE ROW LEVEL SECURITY;

COMMIT;
