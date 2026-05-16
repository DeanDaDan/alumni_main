# Sistem Manajemen Data Alumni

Aplikasi berbasis web untuk mengelola, menyimpan, dan memonitor data alumni secara terstruktur. Sistem ini dilengkapi dengan fitur autentikasi *multi-level* untuk membatasi akses berdasarkan peran pengguna (*User*, *Admin*, dan *Superadmin*).

## ✨ Fitur Utama

* **Multi-Level Login:** Akses sistem yang dibedakan berdasarkan *role* (Superadmin, Admin, User).
* **CRUD Data Alumni:** Fitur lengkap untuk Menambah, Membaca, Memperbarui, dan Menghapus data alumni.
* **Manajemen Pengguna:** Superadmin/Admin dapat mengelola hak akses dan akun pengguna lain (Tambah, Edit, Hapus User).
* **Data Generator:** Dilengkapi dengan skrip Python (`dataGenerate.py`) untuk men-*generate* data *dummy* alumni dalam jumlah besar (untuk keperluan *testing*).
* **Tampilan Responsif:** Antarmuka yang ditata menggunakan CSS kustom untuk memberikan pengalaman pengguna yang baik.

## 🛠️ Teknologi yang Digunakan

* **Frontend:** HTML5, CSS3 (Native)
* **Backend:** PHP (Native)
* **Database:** MySQL / MariaDB
* **Scripting Tambahan:** Python (Untuk *data generation*)

## 📂 Struktur Direktori Utama

```text
MANAJEMEN-DATA-ALUMNI/
│
├── database/               # Berisi file SQL dan skrip Python pembuat data
│   ├── 1000_alumni.sql     # Backup data dummy alumni
│   ├── db_alumni.sql       # Skema utama database
│   └── dataGenerate.py     # Skrip Python pembuat data dummy
├── style/                  # Kumpulan file CSS
│   ├── dashboard.css
│   ├── edit.css
│   ├── index.css
│   └── tambah.css
├── admin/                  # Modul khusus akses Admin
├── superadmin/             # Modul khusus akses Superadmin
├── user/                   # Modul khusus akses User biasa
├── screenshoot/            # Folder dokumentasi tangkapan layar
│
# Core PHP Files
├── koneksi.php             # Konfigurasi koneksi ke database MySQL
├── login.php               # Halaman login
├── logout.php              # Skrip untuk mengakhiri sesi (logout)
├── index.php               # Halaman utama / Landing page
├── dashboard_admin.php     # Dashboard khusus Admin
├── dashboard_user.php      # Dashboard khusus User
├── tambah.php              # Form tambah data alumni
├── edit.php                # Form edit data alumni
├── delete.php              # Skrip hapus data alumni
├── users.php               # Daftar pengguna sistem
├── tambah_user.php         # Form tambah pengguna baru
├── edit_user.php           # Form edit data pengguna
└── delete_user.php         # Skrip hapus pengguna
```

## 🗄️ Struktur Database

Sistem ini menggunakan database bernama `db_alumni` yang terdiri dari dua tabel utama:

* **`alumni`**: Menyimpan detail informasi alumni (NIM, Nama, Angkatan, Jurusan, Email, No. HP, Pekerjaan, Perusahaan, Alamat, dll).
* **`users`**: Menyimpan kredensial login dan *role* dari pengguna sistem.

## 🚀 Cara Instalasi & Penggunaan

Ikuti langkah-langkah berikut untuk menjalankan proyek ini di mesin lokal kamu:

### Persyaratan Sistem
* Web Server lokal (seperti **XAMPP**, **MAMP**, atau **Laragon**) yang mendukung PHP dan MySQL.

### Langkah-langkah

1.  **Clone / Unduh Proyek**
    Simpan folder `MANAJEMEN-DATA-ALUMNI` ke dalam direktori *document root* web server kamu (misalnya: `C:\xampp\htdocs\` untuk XAMPP).

2.  **Siapkan Database**
    * Buka phpMyAdmin (biasanya di `http://localhost/phpmyadmin`).
    * Buat database baru dengan nama `db_alumni`.
    * Import file `db_alumni.sql` yang berada di dalam folder `database/` ke dalam database yang baru dibuat.
    * *(Opsional)* Jika ingin langsung menggunakan data *dummy*, import juga file `1000_alumni.sql`.

3.  **Konfigurasi Koneksi**
    Buka file `koneksi.php` dan pastikan kredensial database sudah sesuai dengan pengaturan lokal kamu (biasanya username `root` dan password dikosongkan).

4.  **Jalankan Aplikasi**
    Buka browser dan akses URL: `http://localhost/MANAJEMEN-DATA-ALUMNI/login.php` (atau sesuaikan dengan nama folder *root* kamu).

### 🔑 Akun Default (Testing)

Gunakan akun berikut untuk mencoba login berdasarkan *role* (sesuai *insert* data di SQL):

| Role | Username | Password |
| :--- | :--- | :--- |
| **Superadmin** | `superadmin` | `superadmin` |
| **Admin** | `admin` | `admin` |
| **User** | `user` | `user` |

---

**Dikembangkan oleh:** Syamil Cholid Atsani | SMK Telkom Lampung