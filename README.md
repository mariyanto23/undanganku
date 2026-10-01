# Undangan Art 55 — Personal Wedding Invitation

Versi ini khusus **satu undangan pribadi**, bukan SaaS dan bukan multi-tenant.

## URL
- Publik: `https://domainanda.com/`
- Nama tamu: `https://domainanda.com/?to=Mariyanto`
- Admin: `https://domainanda.com/admin/`

## Teknologi
PHP 8+, MySQL 8+, HTML/CSS/Vanilla JS.

## Instalasi
1. Upload semua file ke hosting.
2. Buat database MySQL.
3. Import `database/schema.sql`.
4. Sesuaikan `config/config.php` atau environment `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_PORT`.
5. Pastikan folder `uploads/` writable.
6. Buka `/admin/`.

Login awal: `admin` / `password`.

## Modul admin
Pengaturan, Mempelai, Acara, Love Story, Gallery, Rekening, RSVP/Ucapan.

Tidak ada `user_id`, `slug`, tabel `undangan`, subscription, tenant, atau route `/u/{slug}`.


## Art 55 original-template mode
Halaman publik menggunakan `template/art55-template.html` dari source Art 55. Semua `href`/`src` eksternal tetap menunjuk ke host asli (`the.invisimple.id`, `envitto.invisimple.id`, Google Fonts/CDN). Asset visual tidak disalin ke project. `index.php` hanya menyuntikkan data PHP/MySQL dan nama tamu `?to=`.
