<?php
require_once __DIR__ . '/_init.php';

$u = $pdo->query('SELECT * FROM settings WHERE id=1')->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $cover = save_upload('cover', 'cover');
    $couplePhoto = save_upload('couple_photo', 'couple');
    $music = save_upload('music', 'music');
    $sql = 'UPDATE settings SET judul=?,nama_wanita=?,nama_pria=?,tanggal=?,quote=?,quote_source=?,opening_text=?,closing_text=?,live_url=?,dresscode_text=?,status=?';
    $params = [
        trim($_POST['judul']), trim($_POST['nama_wanita']), trim($_POST['nama_pria']), $_POST['tanggal'],
        trim($_POST['quote']), trim($_POST['quote_source']), trim($_POST['opening_text']), trim($_POST['closing_text']),
        trim($_POST['live_url']), trim($_POST['dresscode_text']), $_POST['status'],
    ];

    if ($cover) {
        $sql .= ',cover_image=?';
        $params[] = $cover;
    }
    if ($couplePhoto) {
        $sql .= ',couple_photo=?';
        $params[] = $couplePhoto;
    }
    if ($music) {
        $sql .= ',music_file=?';
        $params[] = $music;
    }

    $pdo->prepare($sql . ' WHERE id=1')->execute($params);
    flash('success', 'Pengaturan disimpan.');
    redirect('/admin/pengaturan.php');
}

$couplePhoto = public_image($u['couple_photo'] ?? '');
admin_header('Pengaturan');
?>
<div class="admin-card">
    <h1>Pengaturan Undangan</h1>
    <form method="post" enctype="multipart/form-data" class="form">
        <?= csrf_field() ?>
        <div class="grid2">
            <div class="field"><label>Judul</label><input name="judul" value="<?= e($u['judul']) ?>"></div>
            <div class="field"><label>Tanggal Pernikahan</label><input type="date" name="tanggal" value="<?= e($u['tanggal']) ?>"></div>
            <div class="field"><label>Nama Wanita</label><input name="nama_wanita" value="<?= e($u['nama_wanita']) ?>"></div>
            <div class="field"><label>Nama Pria</label><input name="nama_pria" value="<?= e($u['nama_pria']) ?>"></div>
            <div class="field"><label>Status</label><select name="status"><option value="published" <?= $u['status'] === 'published' ? 'selected' : '' ?>>published</option><option value="draft" <?= $u['status'] === 'draft' ? 'selected' : '' ?>>draft</option></select></div>
            <div class="field"><label>Live URL</label><input name="live_url" value="<?= e($u['live_url']) ?>"></div>
        </div>
        <div class="field"><label>Quote</label><textarea name="quote"><?= e($u['quote']) ?></textarea></div>
        <div class="field"><label>Sumber Quote</label><input name="quote_source" value="<?= e($u['quote_source']) ?>"></div>
        <div class="field"><label>Teks Pembuka</label><textarea name="opening_text"><?= e($u['opening_text']) ?></textarea></div>
        <div class="field"><label>Teks Penutup</label><textarea name="closing_text"><?= e($u['closing_text']) ?></textarea></div>
        <div class="field"><label>Dresscode</label><textarea name="dresscode_text"><?= e($u['dresscode_text']) ?></textarea></div>
        <div class="grid2">
            <div class="field"><label>Cover</label><input type="file" name="cover" accept="image/*"></div>
            <div class="field">
                <label>Foto Pasangan</label>
                <?php if ($couplePhoto): ?><img class="admin-photo-preview" src="<?= e($couplePhoto) ?>" alt="Foto pasangan saat ini"><?php endif; ?>
                <input type="file" name="couple_photo" accept="image/*">
            </div>
            <div class="field"><label>Musik MP3</label><input type="file" name="music" accept="audio/mpeg"></div>
        </div>
        <button class="btn">Simpan</button>
    </form>
</div>
<?php admin_footer(); ?>
