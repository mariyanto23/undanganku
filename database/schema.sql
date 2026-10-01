CREATE DATABASE IF NOT EXISTS undangan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE undangan;

DROP TABLE IF EXISTS guestbook, gallery, rekening, love_story, acara, mempelai, settings, admin_users;

CREATE TABLE admin_users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 username VARCHAR(60) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 name VARCHAR(120) NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE settings (
 id TINYINT UNSIGNED PRIMARY KEY,
 judul VARCHAR(180) NOT NULL DEFAULT 'The Wedding Of',
 nama_wanita VARCHAR(120) NOT NULL,
 nama_pria VARCHAR(120) NOT NULL,
 tanggal DATE NOT NULL,
 cover_image VARCHAR(255) DEFAULT NULL,
 background_image VARCHAR(255) DEFAULT NULL,
 music_file VARCHAR(255) DEFAULT NULL,
 quote TEXT DEFAULT NULL,
 quote_source VARCHAR(120) DEFAULT NULL,
 opening_text TEXT DEFAULT NULL,
 closing_text TEXT DEFAULT NULL,
 live_url VARCHAR(500) DEFAULT NULL,
 dresscode_text TEXT DEFAULT NULL,
 status ENUM('draft','published') NOT NULL DEFAULT 'published',
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE mempelai (
 id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tipe ENUM('wanita','pria') NOT NULL UNIQUE,
 nama_panggilan VARCHAR(120) NOT NULL,
 nama_lengkap VARCHAR(180) NOT NULL,
 ayah VARCHAR(150) DEFAULT NULL,
 ibu VARCHAR(150) DEFAULT NULL,
 instagram VARCHAR(150) DEFAULT NULL,
 foto VARCHAR(255) DEFAULT NULL
);

CREATE TABLE acara (
 id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 jenis ENUM('akad','resepsi') NOT NULL UNIQUE,
 tanggal DATE NOT NULL,
 jam_mulai TIME DEFAULT NULL,
 jam_selesai TIME DEFAULT NULL,
 nama_lokasi VARCHAR(255) DEFAULT NULL,
 alamat TEXT DEFAULT NULL,
 maps_url VARCHAR(600) DEFAULT NULL
);

CREATE TABLE love_story (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tanggal_label VARCHAR(100) NOT NULL,
 judul VARCHAR(180) DEFAULT NULL,
 cerita TEXT NOT NULL,
 urutan INT NOT NULL DEFAULT 0
);

CREATE TABLE gallery (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 file_path VARCHAR(255) NOT NULL,
 caption VARCHAR(255) DEFAULT NULL,
 urutan INT NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE rekening (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 bank VARCHAR(60) NOT NULL,
 nomor_rekening VARCHAR(100) NOT NULL,
 atas_nama VARCHAR(150) NOT NULL,
 urutan INT NOT NULL DEFAULT 0
);

CREATE TABLE guestbook (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nama VARCHAR(150) NOT NULL,
 ucapan TEXT NOT NULL,
 kehadiran ENUM('hadir','tidak_hadir','belum_konfirmasi') NOT NULL DEFAULT 'belum_konfirmasi',
 jumlah_tamu TINYINT UNSIGNED NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_guestbook_created (created_at)
);

INSERT INTO admin_users(username,password_hash,name) VALUES
('admin','$2y$12$IGc0FYufbHiWTtzvbN3W.eMdgrabmZKkzE2MtkL1P.Fy8T/nKlEzS','Administrator');

INSERT INTO settings(id,judul,nama_wanita,nama_pria,tanggal,quote,quote_source,opening_text,closing_text,live_url,dresscode_text,status) VALUES
(1,'The Wedding Of Putri & Andika','Putri','Andika','2027-12-28','Dan di antara tanda-tanda (kebesaran)-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan Dia menjadikan di antaramu rasa kasih dan sayang.','Q.S Ar-Rum : 21','Maha Suci Allah yang telah menciptakan makhluk-Nya berpasang-pasangan. Ya Allah semoga ridho-Mu tercurah mengiringi pernikahan kami.','Merupakan suatu kebahagiaan dan kehormatan bagi kami, apabila Bapak/Ibu/Saudara/i, berkenan hadir dan memberikan do’a restu kepada kami.','https://youtube.com/','Kami dengan hormat menganjurkan tamu kami untuk mengenakan warna-warna ini untuk hari istimewa kami.','published');

INSERT INTO mempelai(tipe,nama_panggilan,nama_lengkap,ayah,ibu,instagram) VALUES
('wanita','Putri','Putri Cantika Sari','Abdul Rozak','Adelia Marni','user_ig_wanita'),
('pria','Andika','Putra Andika Pratama','Deni Bastian','Aisha Dania','user_ig_pria');

INSERT INTO acara(jenis,tanggal,jam_mulai,jam_selesai,nama_lokasi,alamat,maps_url) VALUES
('akad','2027-12-28','08:00:00',NULL,'Menara 165','Jl. TB Simatupang Jakarta Selatan','https://maps.app.goo.gl/TsZCeupoF4p6bksT6'),
('resepsi','2027-12-28','09:00:00','13:00:00','Menara 165','Jl. TB Simatupang Jakarta Selatan','https://maps.app.goo.gl/TsZCeupoF4p6bksT6');

INSERT INTO love_story(tanggal_label,judul,cerita,urutan) VALUES
('25 AGUSTUS 2023','Awal Bertemu','Berawal dari tempat pekerjaan Cianjur-2023, kami mengenal satu sama lain dan belum ada benih cinta kala itu, hanya sebatas teman kerja.',1),
('03 JUNI 2024','Menjalin Hubungan','Setelah cukup mengenal satu sama lain, kurang lebih satu tahun kami menjalin hubungan. Akhirnya kami memutuskan untuk melanjutkan ke hubungan yang lebih serius dan mempertemukan kedua keluarga.',2),
('29 DESEMBER 2025','Menjadi Keluarga','Sampai tanggal ini kami melaksanakan akad terlebih dahulu dan akhirnya kami mengubah status hingga menjadi pasangan suami istri. Semoga Allah SWT memberikan keberkahan pernikahan ini.',3);

INSERT INTO rekening(bank,nomor_rekening,atas_nama,urutan) VALUES
('BCA','123123123','Putri Cantika Sari',1),('BNI','321321321','Putra Andika Pratama',2),('ALLIANCE BANK','123111222','Putra Andika Pratama',3);
