<?php
require_once __DIR__ . '/../_init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        $pdo->prepare('DELETE FROM guestbook WHERE id=?')->execute([$id]);
        flash('success', 'RSVP berhasil dihapus.');
    }
    redirect('/admin/rsvp/');
}

$rows = $pdo->query('SELECT * FROM guestbook ORDER BY id DESC')->fetchAll();
admin_header('RSVP');
?>
<div class="admin-card">
    <div class="admin-card-heading">
        <h1>RSVP & Ucapan</h1>
        <span><?= count($rows) ?> entri</span>
    </div>
    <?php if (!$rows): ?>
        <p class="admin-empty">Belum ada RSVP.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table rsvp-table">
                <thead>
                    <tr><th>Nama</th><th>Ucapan</th><th>Kehadiran</th><th>Tamu</th><th>Waktu</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td data-label="Nama"><?= e($r['nama']) ?></td>
                            <td data-label="Ucapan"><?= nl2br(e($r['ucapan'])) ?></td>
                            <td data-label="Kehadiran"><?= e($r['kehadiran']) ?></td>
                            <td data-label="Tamu"><?= (int) $r['jumlah_tamu'] ?></td>
                            <td data-label="Waktu"><?= e($r['created_at']) ?></td>
                            <td data-label="Aksi">
                                <form method="post" class="delete-rsvp-form" onsubmit="return confirm('Hapus RSVP ini?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                    <button class="btn danger-btn" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php admin_footer(); ?>
