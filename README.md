# ☁️ Website Monitoring Polusi Udara Kampus (UBP AirMonitor)

Aplikasi berbasis web untuk memantau kualitas udara secara realtime menggunakan API AQICN, dilengkapi dengan sistem peringatan dini via Email dan analisis tren polusi.

Dibuat untuk memenuhi Final project dari matakuliah Pemprograman Web, Pemprograman Berorientasi Objek, Analisis dan Desain Berorientasi Objek  .

## 🚀 Fitur Utama
* **Realtime Monitoring:** Menampilkan AQI (Air Quality Index) dan Suhu terkini.
* **Early Warning System:** Mengirim notifikasi email otomatis ke mahasiswa jika udara **BERBAHAYA**.
* **Data Visualization:** Grafik Tren (Line Chart) dan Komposisi (Pie Chart) menggunakan Chart.js.
* **Caching System:** Hemat kuota API dengan sistem TTL Cache (1 Jam).
* **User Management:** Login/Register Mahasiswa dan Panel Admin lengkap.
* **Export Data:** Unduh laporan riwayat polusi dalam format CSV.

## 🛠️ Teknologi yang Digunakan
* **Backend:** PHP Native (OOP Architecture)
* **Database:** MySQL / MariaDB
* **Frontend:** Bootstrap 5 & Chart.js
* **External API:** AQICN (World Air Quality Index Project)
* **Library:** PHPMailer (via Composer)

---

## 💻 Cara Install (Localhost / XAMPP)

1.  **Clone / Download** folder proyek ini ke dalam folder `htdocs`.
2.  **Database:**
    * Buka phpMyAdmin.
    * Buat database baru bernama `db_udara`.
    * Import file `database.sql` yang ada di folder proyek.
3.  **Konfigurasi Environment:**
    * Cari file bernama `.env.example` (atau buat baru `.env`).
    * Isi konfigurasi berikut:
        ```env
        DB_HOST=localhost
        DB_USER=root
        DB_PASS=
        DB_NAME=db_udara
        
        API_TOKEN=596d39cb3f64efecfd8928449884552956ded22e
        
        SMTP_HOST=smtp.gmail.com
        SMTP_USER=rahmanhabibi517@gmail.com
        SMTP_PASS=efwz yjuh txas tmje
        SMTP_PORT=465

        APP_URL=http://localhost/Final_project/Fase8
        ```
4.  **Install Dependencies:**
    * Buka Terminal/CMD di folder proyek.
    * Jalankan: `composer install` (untuk mengunduh PHPMailer).
    * *Jika tidak pakai composer, pastikan folder `vendor` sudah tercopy.*
5.  **Jalankan:**
    * Buka browser: `http://localhost/Final_project/Fase5/index.php`

---

## 🤖 Cara Menjalankan Logger (Robot Pencatat)

Agar data tersimpan otomatis dan notifikasi berjalan, script `logger.php` harus dijalankan.

**Di Windows (Manual):**
Buka CMD, ketik:
`php logger.php`

**Di Hosting (Otomatis):**
Setting **Cron Job** di cPanel dengan interval "Once Per Hour":
`/usr/local/bin/php /home/username/public_html/logger.php`

---

## 🔑 Akun Pengujian (Demo)

**1. Admin:**
* **Email:** `rahmanhabibi517@gmail.com`
* **Password:** `admin123`
* *Akses:* `Auth/login.php`

**2. User:**
* **Email:** `mhs@ubp.ac.id` (Contoh)
* **Password:** `12345` (Contoh jika sudah didaftarkan)
* *Akses:* `Auth/user_login.php`

---

## 📂 Struktur Folder
* `/Admin` - Halaman Panel Admin (Dashboard, Logs, Data).
* `/Auth` - Halaman Login & Register.
* `/Class` - Core Logic (Database, API, Service, Repository).
* `/Config` - Koneksi Database Legacy.
* `/vendor` - Library Pihak Ketiga (PHPMailer).