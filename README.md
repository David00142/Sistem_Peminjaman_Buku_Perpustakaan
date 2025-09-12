BoBook App

Aplikasi Bobook adalah sistem manajemen perpustakaan yang mempermudah pustakawan dan anggota dalam proses peminjaman buku dan booking. Aplikasi ini dirancang untuk memastikan ketersediaan buku dan memberikan kemudahan akses bagi seluruh anggota perpustakaan.
Fitur Utama
Sistem Booking Buku: Anggota dapat melakukan booking buku yang ingin dipinjam. Buku yang di-booking akan secara otomatis ter-verifikasi oleh pustakawan.
Notifikasi & Pembatalan Otomatis: Anggota diberi waktu 2 hari untuk mengambil buku yang di-booking. Jika melewati batas waktu tersebut, sistem akan membatalkan booking secara otomatis dan memperbarui stok buku.
Dashboard Anggota: Anggota dapat melihat buku yang tersedia, status buku yang sedang di-booking, dan buku yang sedang dipinjam.
Manajemen Denda: Sistem akan menghitung denda sebesar Rp2.000 per hari untuk buku yang terlambat dikembalikan.
Validasi Pengguna: Pustakawan dapat memvalidasi data anggota melalui email untuk memastikan keaslian.


//Instalasi//
Ikuti langkah-langkah berikut untuk menginstal dan menjalankan aplikasi di komputer lokal Anda:
Kloning repositori:
git clone <URL_REPOSITORY>


Masuk ke direktori proyek:
cd <nama-proyek>


Instal dependensi PHP & JavaScript:
composer install
npm install


Salin file konfigurasi .env:
cp .env.example .env


Buat application key:
php artisan key:generate


Konfigurasi database:
Buka file .env dan atur konfigurasi database Anda (DB_DATABASE, DB_USERNAME, DB_PASSWORD).
Jalankan migrasi database:
php artisan migrate


Jalankan server pengembangan Laravel:
php artisan serve


//Penggunaan//
Halaman Anggota
Login/Register: Anggota dapat masuk atau mendaftar untuk mendapatkan akses.
Home: Menampilkan total buku yang tersedia, riwayat booking dan peminjaman, serta buku-buku populer.
Available Books: Menampilkan daftar buku yang tersedia, beserta jumlah total, yang terpinjam, dan yang di-booking.
Booked: Menampilkan buku yang sedang di-booking oleh anggota, lengkap dengan batas waktu pengambilan.
Borrowed: Menampilkan daftar buku yang sedang dipinjam, beserta batas waktu pengembalian.
Penalty: Menampilkan denda yang harus dibayar jika terjadi keterlambatan pengembalian buku.
Halaman Pustakawan (Admin)
Data User: Pustakawan dapat melihat data anggota untuk keperluan validasi.
Add Book: Halaman untuk pustakawan menambahkan buku baru ke dalam sistem.
Booked: Halaman untuk pustakawan melihat daftar buku yang di-booking oleh anggota. Di sini, pustakawan dapat memverifikasi booking tersebut.
Borrowed: Menampilkan semua buku yang sedang dalam status terpinjam.
Penalty: Menampilkan data anggota yang memiliki denda.

//Arsitektur//
Aplikasi ini dibangun di atas framework Laravel yang menggunakan arsitektur Model-View-Controller (MVC).
Model: Mengelola data database (misalnya, Book, User, Borrowing).
View: Mengelola tampilan antarmuka pengguna, termasuk halaman-halaman untuk anggota dan pustakawan.
Controller: Menghubungkan Model dan View, memproses request dari pengguna, dan menjalankan logika aplikasi.

//Kontribusi//
Proyek ini adalah hasil kolaborasi antara tim front-end dan back-end.
[David]: Mengimplementasikan logika front-end dan back-end untuk halaman Home, Register, Login, Welcome, dan Available Books.
[Vincent]: Mengimplementasikan logika front-end dan back-end untuk manajemen Book, Penalty, dan Borrowed.

//Lisensi//
Proyek ini dilisensikan di bawah MIT License.
MIT License adalah pilihan yang ideal karena sifatnya yang permisif, memungkinkan siapa pun untuk menggunakan, memodifikasi, dan mendistribusikan kode ini tanpa batasan ketat, selama lisensi dan hak cipta asli disertakan.
