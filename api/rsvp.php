<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=UTF-8');

function respond_rsvp(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond_rsvp(405, ['message' => 'Metode tidak diizinkan.']);
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '') {
    $originHost = parse_url($origin, PHP_URL_HOST);
    $requestHost = preg_replace('/:\\d+$/', '', $_SERVER['HTTP_HOST'] ?? '');
    if (!$originHost || !hash_equals($requestHost, $originHost)) {
        respond_rsvp(403, ['message' => 'Asal permintaan tidak valid.']);
    }
}

if (trim($_POST['website'] ?? '') !== '') {
    respond_rsvp(422, ['message' => 'Data tidak valid.']);
}

$nama = trim($_POST['nama'] ?? '');
$ucapan = trim($_POST['ucapan'] ?? '');
$kehadiran = $_POST['kehadiran'] ?? 'belum_konfirmasi';
$jumlahTamu = max(1, min(10, (int) ($_POST['jumlah_tamu'] ?? 1)));

if ($nama === '' || mb_strlen($nama) > 150 || mb_strlen($ucapan) < 2 || mb_strlen($ucapan) > 5000) {
    respond_rsvp(422, ['message' => 'Nama dan ucapan harus diisi dengan benar.']);
}
if (!in_array($kehadiran, ['hadir', 'tidak_hadir', 'belum_konfirmasi'], true)) {
    $kehadiran = 'belum_konfirmasi';
}

$statement = $pdo->prepare('INSERT INTO guestbook(nama, ucapan, kehadiran, jumlah_tamu) VALUES(?,?,?,?)');
$statement->execute([$nama, $ucapan, $kehadiran, $jumlahTamu]);

respond_rsvp(201, [
    'message' => 'Terima kasih, konfirmasi Anda sudah tersimpan.',
    'entry' => [
        'nama' => $nama,
        'ucapan' => $ucapan,
        'kehadiran' => $kehadiran,
        'jumlah_tamu' => $jumlahTamu,
        'created_at' => date('Y-m-d H:i:s'),
    ],
]);
