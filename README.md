# Sistem Manajemen User

Aplikasi manajemen pengguna berbasis web yang komprehensif, dibangun dengan PHP native, MySQL, dan Bootstrap 5 

## Fitur

### Fitur Utama
- **Sistem Autentikasi**
  - Login aman dengan bcrypt password hashing
  - Manajemen sesi dengan timeout otomatis
  - Tracking percobaan login (pencegahan brute force)
  - Proteksi CSRF di semua form

- **Operasi CRUD Pengguna**
  - Create, Read, Update, Delete pengguna
  - Pencarian dan filter berdasarkan email, peran, dan status
  - Pagination (20 pengguna per halaman)
  - Kontrol akses berbasis peran

- **Izin Berbasis Peran**
  - **Superadmin**: Akses penuh ke semua fitur
  - **Admin**: Kelola pengguna (kecuali superadmin), lihat log
  - **Operator**: Lihat dan edit pengguna
  - **Validator**: Hanya dapat melihat

### Fitur Tambahan
- **Dashboard**
  - Statistik dan analitik pengguna
  - Aktivitas terkini
  - Tampilan clean dan modern

- **Log Audit Aktivitas**
  - Tracking semua aksi pengguna (login, logout, operasi CRUD)
  - Filter berdasarkan pengguna, aksi, rentang tanggal
  - Tracking alamat IP

- **Profil Pengguna**
  - Edit profil sendiri
  - Ubah kata sandi dengan validasi
  - Lihat riwayat aktivitas personal

- **Keamanan**
  - Validasi kekuatan kata sandi (min 8 karakter, huruf besar, kecil, dan angka)
  - Proteksi token CSRF
  - Pencegahan XSS (htmlspecialchars)
  - Pencegahan SQL injection (prepared statements)
  - Timeout sesi (30 menit)
  - Tidak dapat hapus/nonaktifkan akun sendiri

- **Desain Responsive**
  - Mobile (390px): Single column, hamburger menu
  - Tablet (768px): Layout 2 kolom
  - Laptop (1280px+): Layout penuh, multi-kolom


## Technology Stack

- **Backend**: PHP 8.x with PDO
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **CSS Framework**: Bootstrap 5
- **Charts**: Chart.js

## Installation

### Prerequisites
- PHP 8.0 atau lebih baru
- MySQL 5.7+ / MariaDB 10.2+
- Web server (Apache/Nginx) - disarankan Laragon untuk Windows

### Setup Steps

1. **Kloning atau Unduh Proyek**
   - Letakkan proyek di direktori web server


2. **Buat Database**
   ```sql
   CREATE DATABASE tugaskominfo;
   ```

3. **Import Database Schema**
   - Buka phpMyAdmin, Adminer, HeidiSQL, atau klien MySQL lain
   - Pilih database tugaskominfo
   - Impor berkas `database.sql` 
   
   Atau via baris perintah:
   - mysql -u root -p tugaskominfo < database.sql
   

3a. **Jalankan Setup Awal (PENTING)**
   - Setelah mengimpor database, buka browser
   - Arahkan ke:: `http://localhost/tugaskominfo/setup.php`
   - Klik tombol “Run Setup Now”
   - Proses ini akan membuat user admin dengan hash password yang kompatibel dengan versi PHP Anda

4. **Konfigurasi Koneksi Database**
   - Buka `config/database.php`
   - Update jika diperlukan
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'tugaskominfo');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

5. **Konfigurasi Koneksi Database**
   - Buka config/config.php`
   - Setel APP_URL::
   ```php
   define('APP_URL', 'http://localhost/tugaskominfo');
   ```

6. **Akses Aplikasi**
   - Open your browser
   - Navigate to: `http://localhost/tugaskominfo`

## Login Bawaan

- **Email**: admin@example.com
- **Password**: Admin@123

**Important**: Penting: Ubah password bawaan setelah login pertama!

## Struktur Direktori

```
tugaskominfo/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── script.js
├── config/
│   ├── config.php
│   └── database.php
├── includes/
│   ├── auth.php
│   ├── footer.php
│   ├── functions.php
│   └── header.php
├── logs/
│   └── index.php
├── users/
│   ├── create.php
│   ├── delete.php
│   ├── edit.php
│   └── index.php
├── dashboard.php
├── database.sql
├── index.php
├── login.php
├── logout.php
├── profile.php
└── README.md
```

## Skema Basis Data

### Tabel users
```sql
- user_id (INT, PRIMARY KEY, AUTO_INCREMENT)
- email (VARCHAR 255, UNIQUE)
- password_hash (VARCHAR 255)
- role (ENUM: 'superadmin', 'admin', 'operator', 'validator')
- is_active (TINYINT 1)
- created_at (DATETIME)
- updated_at (DATETIME)
```

### Tabel activity_logs
```sql
- log_id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_id (INT, FOREIGN KEY)
- action (VARCHAR 255)
- description (TEXT)
- ip_address (VARCHAR 45)
- created_at (DATETIME)
```

### Tabel login_attempts
```sql
- attempt_id (INT, PRIMARY KEY, AUTO_INCREMENT)
- email (VARCHAR 255)
- ip_address (VARCHAR 45)
- success (TINYINT 1)
- attempted_at (DATETIME)
```

## Fitur Keamanan

1. **Password Security**
   - Hashing bcrypt dengan PASSWORD_BCRYPT
   - Minimal 8 karakter
   - Wajib mengandung huruf besar, huruf kecil, dan angka

2. **Session Security**
   - Cookie hanya-HTTP (HTTP-only)
   - Regenerasi sesi setelah login
   - Timeout tidak aktif 30 menit

3. **Input Validation**
   - Validasi email
   - Validasi kekuatan password
   - Verifikasi token CSRF
   - Pencegahan XSS

4. **Database Security**
   - Prepared statements (PDO)
   - Tanpa query SQL mentah langsung
   - Foreign key constraints

5. **Kontrol Akses**
   - Izin berbasis peran (role-based)
   - Pelacakan percobaan login
   - Pencegahan brute force (maks. 5 percobaan dalam 15 menit)

## Panduan Penggunaan

### Menambahkan Pengguna Baru
1. Login sebagai Superadmin atau Admin
2. Buka menu Users
3. Klik Add New User
4. Isi form (email, password, peran, status)
5. Klik Create User

### Mengedit Pengguna
1. Buka menu Users
2. Klik ikon pensil pada baris pengguna
3. Perbarui informasi
4. Click "Update User"

### Menghapus Pengguna
1. Buka menu Users
2. Klik ikon tempat sampah pada baris pengguna
3. Konfirmasi penghapusan
4. Catatan: Anda tidak bisa menghapus akun sendiri

### Melihat Log Aktivitas
1. Login sebagai Superadmin atau Admin
2. Buka menu Activity Logs
3. Gunakan filter untuk mencari berdasarkan aksi, pengguna, atau rentang tanggal

### Updating Your Profile
1. Klik email Anda di navbar
2. Pilih Profile
3. Ubah email atau password
4. Klik Update Profile


## Credits

Developed for Tugas Kominfo
- PHP Native (No Frameworks)
- Bootstrap 5
- Bootstrap Icons

## License

Proyek ini dibuat untuk tujuan tugas programmer diskominfo.
