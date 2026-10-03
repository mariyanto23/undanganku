<?php
require_once __DIR__ . '/_init.php';

$u = $pdo->query('SELECT * FROM settings WHERE id=1')->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $cover = save_upload('cover', 'cover');
    $couplePhoto = save_upload('couple_photo', 'couple');
    $music = save_upload('music', 'music');

    $sql = 'UPDATE settings SET judul=?, nama_wanita=?, nama_pria=?, tanggal=?, quote=?, quote_source=?, opening_text=?, closing_text=?, live_url=?, dresscode_text=?, status=?';

    $params = [
        trim($_POST['judul'] ?? ''),
        trim($_POST['nama_wanita'] ?? ''),
        trim($_POST['nama_pria'] ?? ''),
        $_POST['tanggal'] ?? '',
        trim($_POST['quote'] ?? ''),
        trim($_POST['quote_source'] ?? ''),
        trim($_POST['opening_text'] ?? ''),
        trim($_POST['closing_text'] ?? ''),
        trim($_POST['live_url'] ?? ''),
        trim($_POST['dresscode_text'] ?? ''),
        $_POST['status'] ?? 'draft',
    ];

    if ($cover) {
        $sql .= ', cover_image=?';
        $params[] = $cover;
    }

    if ($couplePhoto) {
        $sql .= ', couple_photo=?';
        $params[] = $couplePhoto;
    }

    if ($music) {
        $sql .= ', music_file=?';
        $params[] = $music;
    }

    $pdo->prepare($sql . ' WHERE id=1')->execute($params);

    flash('success', 'Pengaturan disimpan.');
    redirect('/admin/pengaturan.php');
}

$couplePhoto = public_image($u['couple_photo'] ?? '');

admin_header('Pengaturan');
?>

<div class="admin-card settings-card">

    <div class="settings-header">
        <div>
            <h1>Pengaturan Undangan</h1>
            <p>Kelola informasi utama, foto, dan musik undangan.</p>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data" class="form settings-form">

        <?= csrf_field() ?>

        <!-- INFORMASI UTAMA -->
        <div class="settings-section">
            <div class="settings-section-title">
                <h2>Informasi Utama</h2>
                <p>Informasi dasar yang ditampilkan pada undangan.</p>
            </div>

            <div class="grid2 settings-grid">

                <div class="field">
                    <label for="judul">Judul</label>
                    <input
                        id="judul"
                        type="text"
                        name="judul"
                        value="<?= e($u['judul'] ?? '') ?>"
                        placeholder="Contoh: Pernikahan D & M"
                    >
                </div>

                <div class="field">
                    <label for="tanggal">Tanggal Pernikahan</label>
                    <input
                        id="tanggal"
                        type="date"
                        name="tanggal"
                        value="<?= e($u['tanggal'] ?? '') ?>"
                    >
                </div>

                <div class="field">
                    <label for="nama_wanita">Nama Wanita</label>
                    <input
                        id="nama_wanita"
                        type="text"
                        name="nama_wanita"
                        value="<?= e($u['nama_wanita'] ?? '') ?>"
                        placeholder="Nama mempelai wanita"
                    >
                </div>

                <div class="field">
                    <label for="nama_pria">Nama Pria</label>
                    <input
                        id="nama_pria"
                        type="text"
                        name="nama_pria"
                        value="<?= e($u['nama_pria'] ?? '') ?>"
                        placeholder="Nama mempelai pria"
                    >
                </div>

                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="published" <?= ($u['status'] ?? '') === 'published' ? 'selected' : '' ?>>
                            Published
                        </option>
                        <option value="draft" <?= ($u['status'] ?? '') === 'draft' ? 'selected' : '' ?>>
                            Draft
                        </option>
                    </select>
                </div>

                <div class="field">
                    <label for="live_url">Live URL</label>
                    <input
                        id="live_url"
                        type="url"
                        name="live_url"
                        value="<?= e($u['live_url'] ?? '') ?>"
                        placeholder="https://..."
                    >
                </div>

            </div>
        </div>


        <!-- QUOTE -->
        <div class="settings-section">

            <div class="settings-section-title">
                <h2>Quote</h2>
                <p>Kalimat atau kutipan yang ditampilkan pada undangan.</p>
            </div>

            <div class="field">
                <label for="quote">Quote</label>
                <textarea
                    id="quote"
                    name="quote"
                    placeholder="Tulis quote di sini..."
                ><?= e($u['quote'] ?? '') ?></textarea>
            </div>

            <div class="field">
                <label for="quote_source">Sumber Quote</label>
                <input
                    id="quote_source"
                    type="text"
                    name="quote_source"
                    value="<?= e($u['quote_source'] ?? '') ?>"
                    placeholder="Contoh: QS. Ar-Rum: 21"
                >
            </div>

        </div>


        <!-- TEKS UNDANGAN -->
        <div class="settings-section">

            <div class="settings-section-title">
                <h2>Teks Undangan</h2>
                <p>Atur teks pembuka dan penutup pada halaman undangan.</p>
            </div>

            <div class="field">
                <label for="opening_text">Teks Pembuka</label>
                <textarea
                    id="opening_text"
                    name="opening_text"
                    placeholder="Tulis teks pembuka..."
                ><?= e($u['opening_text'] ?? '') ?></textarea>
            </div>

            <div class="field">
                <label for="closing_text">Teks Penutup</label>
                <textarea
                    id="closing_text"
                    name="closing_text"
                    placeholder="Tulis teks penutup..."
                ><?= e($u['closing_text'] ?? '') ?></textarea>
            </div>

            <div class="field">
                <label for="dresscode_text">Dresscode</label>
                <textarea
                    id="dresscode_text"
                    name="dresscode_text"
                    placeholder="Contoh: Dresscode tamu: Biru muda / putih"
                ><?= e($u['dresscode_text'] ?? '') ?></textarea>
            </div>

        </div>


        <!-- MEDIA -->
        <div class="settings-section">

            <div class="settings-section-title">
                <h2>Media Undangan</h2>
                <p>Upload gambar cover, foto pasangan, dan musik.</p>
            </div>

            <div class="grid2 settings-grid">

                <!-- COVER -->
                <div class="field upload-field">

                    <label for="cover">Cover</label>

                    <div class="upload-box">

                        <div class="upload-icon">
                            <span>🖼️</span>
                        </div>

                        <div class="upload-content">
                            <strong>Upload Cover</strong>
                            <small>
                                Pilih gambar cover baru jika ingin menggantinya.
                            </small>
                        </div>

                        <input
                            id="cover"
                            type="file"
                            name="cover"
                            accept="image/*"
                        >

                    </div>

                </div>


                <!-- FOTO PASANGAN -->
                <div class="field upload-field">

                    <label for="couple_photo">Foto Pasangan</label>

                    <?php if ($couplePhoto): ?>

                        <div class="current-photo">

                            <div class="current-photo-header">
                                <span>Foto saat ini</span>
                            </div>

                            <div class="current-photo-image">
                                <img
                                    class="admin-photo-preview"
                                    src="<?= e($couplePhoto) ?>"
                                    alt="Foto pasangan saat ini"
                                >
                            </div>

                        </div>

                    <?php endif; ?>


                    <div class="upload-box">

                        <div class="upload-icon">
                            <span>📷</span>
                        </div>

                        <div class="upload-content">
                            <strong>
                                <?= $couplePhoto ? 'Ganti Foto Pasangan' : 'Upload Foto Pasangan' ?>
                            </strong>

                            <small>
                                Pilih foto baru jika ingin mengganti foto yang sekarang.
                            </small>
                        </div>

                        <input
                            id="couple_photo"
                            type="file"
                            name="couple_photo"
                            accept="image/*"
                        >

                    </div>

                </div>


                <!-- MUSIK -->
                <div class="field upload-field">

                    <label for="music">Musik MP3</label>

                    <div class="upload-box">

                        <div class="upload-icon">
                            <span>🎵</span>
                        </div>

                        <div class="upload-content">
                            <strong>Upload Musik</strong>
                            <small>
                                Gunakan file dengan format MP3.
                            </small>
                        </div>

                        <input
                            id="music"
                            type="file"
                            name="music"
                            accept="audio/mpeg,.mp3"
                        >

                    </div>

                </div>

            </div>

        </div>


        <!-- BUTTON -->
        <div class="settings-actions">

            <button type="submit" class="btn save-settings-btn">
                Simpan Pengaturan
            </button>

        </div>

    </form>

</div>

<?php admin_footer(); ?>