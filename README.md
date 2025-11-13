
# BOBOOK
Sistem Manajemen Perpustakaan Digital 📚 Aplikasi BoBook adalah solusi manajemen perpustakaan digital yang dirancang untuk menyederhanakan proses peminjaman dan booking buku bagi pustakawan dan anggota. Dengan berbagai fitur canggih, aplikasi ini memastikan ketersediaan buku dan memberikan pengalaman yang mudah diakses oleh semua pengguna.


## Fitur

- Sistem Booking Buku

- Notifikasi & Pembatalan Otomatis (2 hari waktu pengambilan)

- Dasbor Anggota

- Manajemen Denda (Rp2.000 per hari)

- Validasi Pengguna

## Instalasi
Copy file repostory yang ingin di jalankan
```
git clone https://github.com/David00142/Sistem_Peminjaman_Buku_Perpustakaan
```
Lakukan konfigurasi awal
```
composter install
```
```
npm install
```
```
npm run dev
``` 
Copy file .env dan .env.example
```
cp .env.example .env
```
Untuk membuat Application Key 
```
php artisan key:generate
```
Di .env ubah DB_DATABASE 
 ```
DB_DATABASE=sistem_peminjaman_buku_perpustakaan
```
Jalankan Migrasi data 
```
php artisan migrate
```
Jalankan Server 
```
php artisan serve
``` 
## Arsitektur 

Aplikasi ini dibangun menggunakan framework Laravel dengan arsitektur Model-View-Controller (MVC).

**Model:** Mengelola interaksi dengan basis data ```(Book, User, Borrowing)```

**View:** Bertanggung jawab atas tampilan antarmuka pengguna, baik untuk anggota maupun pustakawan.

**Controller:** Menghubungkan Model dan View dengan memproses permintaan pengguna dan menjalankan logika aplikasi.

## Kontribusi
Kami saip menyambut setiap bentuk kontribusi dari komunitas! Aplikasi BoBook dirancang untuk memberikan solusi manajemen perpustakaan yang efisien, dan masukan, laporan bug, atau peningkatan fitur dari Anda akan sangat berharga.

**Mengapa Berkontribusi?**

    1. Peningkatan Kualitas: Bantu kami mengidentifikasi dan memperbaiki bug atau celah keamanan untuk memastikan aplikasi berjalan mulus.
    2. Pengembangan Fitur: Sumbangkan ide atau implementasikan fitur baru yang dapat memperkaya fungsionalitas BoBook.
    3. Belajar Bersama: Ini adalah proyek Laravel MVC yang terbuka, menjadikannya tempat yang tepat untuk belajar dan berbagi pengetahuan coding.

**Cara Menggunakan dan Menguji**

    1. Sebelum berkontribusi kode, kami mendorong Anda untuk menguji aplikasi secara lokal:
    2. Ikuti langkah-langkah Instalasi di atas untuk menjalankan BoBook di mesin lokal Anda.
    3. Uji skenario utama (Booking, Peminjaman, Denda) baik sebagai Anggota maupun Pustakawan.
    4. Laporkan setiap masalah atau bug yang Anda temukan melalui bagian Pelaporan Isu di bawah.
**Pelaporan Permasalahan Bug**

Jika Anda menemukan bug, memiliki ide fitur baru, atau menemui kesulitan dalam instalasi, mohon laporkan melalui tab Issues di repositori GitHub ini.

    Saat melaporkan bug, sertakan detail berikut:
    - Langkah-langkah untuk mereproduksi masalah tersebut.
    - Perilaku yang Diharapkan vs. Perilaku Aktual.
    - Versi PHP/Laravel yang Anda gunakan.

Terima kasih atas minat dan dukungan Anda terhadap proyek BoBook! Mari kita kembangkan aplikasi ini bersama-sama!

## License

[MIT](https://github.com/David00142/Sistem_Peminjaman_Buku_Perpustakaan/blob/main/LICENSE)

