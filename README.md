Aplikasi BoBook: Sistem Manajemen Perpustakaan Digital 📚
Aplikasi BoBook adalah solusi manajemen perpustakaan digital yang dirancang untuk menyederhanakan proses peminjaman dan booking buku bagi pustakawan dan anggota. Dengan berbagai fitur canggih, aplikasi ini memastikan ketersediaan buku dan memberikan pengalaman yang mudah diakses oleh semua pengguna.

Fitur Utama
Sistem Booking Buku: Anggota dapat melakukan booking buku secara online, yang kemudian akan diverifikasi oleh pustakawan.

Notifikasi & Pembatalan Otomatis: Anggota diberi waktu 2 hari untuk mengambil buku yang di-booking. Jika tidak diambil, sistem akan secara otomatis membatalkan booking dan memperbarui stok buku.

Dasbor Anggota: Menyediakan tampilan yang jelas mengenai ketersediaan buku, status booking, dan buku yang sedang dipinjam.

Manajemen Denda: Sistem secara otomatis menghitung denda sebesar Rp2.000 per hari untuk buku yang terlambat dikembalikan.

Validasi Pengguna: Pustakawan dapat memvalidasi data anggota melalui email untuk memastikan keaslian.

Instalasi & Penggunaan
Untuk menginstal dan menjalankan aplikasi ini secara lokal, ikuti langkah-langkah berikut:

Langkah Instalasi
Kloning Repositori:

Bash

git clone <URL_REPOSITORY>
Masuk ke Direktori Proyek:

Bash

cd <nama-proyek>
Instal Dependensi:

Bash

composer install
npm install
Salin File Konfigurasi:

Bash

cp .env.example .env
Buat Application Key:

Bash

php artisan key:generate
Konfigurasi dan Migrasi Basis Data:

Atur kredensial basis data Anda (DB_DATABASE, DB_USERNAME, DB_PASSWORD) pada file .env.

Jalankan migrasi basis data:

Bash

php artisan migrate
Jalankan Server:

Bash

php artisan serve
Halaman Penggunaan
Halaman Anggota: Menampilkan opsi Login/Register, serta dasbor untuk melihat status buku (Available, Booked, Borrowed), riwayat peminjaman, dan denda (Penalty).

Halaman Pustakawan (Admin): Menyediakan kontrol penuh atas sistem, termasuk manajemen data pengguna, penambahan buku baru, **verifikasi booking **, dan pemantauan denda yang harus dibayar.

Arsitektur & Kontribusi
Arsitektur
Aplikasi ini dibangun menggunakan framework Laravel dengan arsitektur Model-View-Controller (MVC).

Model: Mengelola interaksi dengan basis data (Book, User, Borrowing).

View: Bertanggung jawab atas tampilan antarmuka pengguna, baik untuk anggota maupun pustakawan.

Controller: Menghubungkan Model dan View dengan memproses permintaan pengguna dan menjalankan logika aplikasi.

Kontribusi Tim
Proyek ini merupakan hasil kolaborasi tim front-end dan back-end yang berdedikasi:

David: Mengimplementasikan logika untuk halaman Home, Register, Login, Welcome, dan Available Books.

Vincent: Mengimplementasikan logika untuk manajemen Book, Penalty, dan Borrowed.

Lisensi
Proyek ini dilisensikan di bawah MIT License, sebuah lisensi permisif yang memungkinkan siapa pun untuk menggunakan, memodifikasi, dan mendistribusikan kode tanpa batasan yang ketat, selama lisensi dan hak cipta asli disertakan. 📝