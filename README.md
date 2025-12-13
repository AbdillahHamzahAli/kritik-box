# Kritik Box Web Full

Ini adalah proyek aplikasi web full-stack yang terdiri dari backend yang dibangun dengan PHP dan frontend yang dibangun dengan Node.js dan Express.

## Deskripsi Proyek

Aplikasi ini tampaknya merupakan platform untuk manajemen bisnis, yang memungkinkan pengguna untuk:

*   Mendaftar dan mengelola bisnis.
*   Melihat dasbor dengan analitik.
*   Memberikan dan menerima umpan balik.
*   Membuat QR Code untuk feedback bisnis.
*   Memproses pembayaran.

## Teknologi yang Digunakan

### Backend

*   PHP
*   Composer untuk manajemen dependensi
*   Midtrans untuk pemrosesan pembayaran
*   Firebase untuk layanan backend tambahan

### Frontend

*   Node.js
*   Express.js sebagai kerangka kerja web
*   EJS (Embedded JavaScript) sebagai mesin template
*   CSS untuk styling

## Fitur

*   **Autentikasi Pengguna**: Login dan registrasi pengguna.
*   **Manajemen Bisnis**: Membuat dan mengelola profil bisnis.
*   **Dasbor**: Menampilkan data dan statistik yang relevan.
*   **Sistem Umpan Balik**: Mengumpulkan dan menampilkan umpan balik dari pengguna.
*   **Integrasi Pembayaran**: Memproses pembayaran melalui Midtrans.

## Struktur Proyek

Proyek ini dibagi menjadi dua bagian utama:

*   `backend/`: Berisi semua logika sisi server yang ditulis dalam PHP.
    *   `src/Controllers`: Menangani logika permintaan HTTP.
    *   `src/Models`: Merepresentasikan struktur data aplikasi.
    *   `src/Routes`: Mendefinisikan rute API.
    *   `src/Services`: Berisi logika bisnis.
*   `frontend/`: Berisi semua logika sisi klien dan rendering tampilan.
    *   `controllers`: Menangani logika untuk setiap halaman.
    *   `views`: Berisi file template EJS untuk rendering HTML.
    *   `public`: Berisi aset statis seperti CSS dan gambar.
    *   `routes`: Mendefinisikan rute untuk setiap halaman.

## Instalasi dan Setup

### Prasyarat

*   PHP dan Composer terinstal.
*   Node.js dan npm terinstal.

### Backend

1.  Masuk ke direktori `backend`:
    ```bash
    cd backend
    ```
2.  Instal dependensi Composer:
    ```bash
    composer install
    ```
3.  Salin `.env.example` menjadi `.env` dan konfigurasikan variabel lingkungan yang diperlukan (misalnya, kredensial database, kunci API Midtrans).
    ```bash
    cp .env.example .env
    ```
4.  Jalankan server pengembangan PHP (misalnya, menggunakan server bawaan PHP atau Apache/Nginx).

### Frontend

1.  Masuk ke direktori `frontend`:
    ```bash
    cd frontend
    ```
2.  Instal dependensi npm:
    ```bash
    npm install
    ```
3.  Salin `.env.example` menjadi `.env` dan konfigurasikan variabel lingkungan yang diperlukan (misalnya, endpoint API backend).
    ```bash
    cp .env.example .env
    ```
4.  Jalankan aplikasi Node.js:
    ```bash
    node app.js
    ```
Aplikasi frontend akan berjalan di `http://localhost:3000` (atau port lain yang dikonfigurasi).
