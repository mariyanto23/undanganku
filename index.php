<?php
require __DIR__.'/config/database.php';
require __DIR__.'/functions/helper.php';

$pdo = db();
$settings = $pdo->query("SELECT * FROM settings WHERE id=1 LIMIT 1")->fetch() ?: [];
$people = [];
foreach ($pdo->query("SELECT * FROM mempelai") as $row) $people[$row['tipe']] = $row;
$events = [];
foreach ($pdo->query("SELECT * FROM acara") as $row) $events[$row['jenis']] = $row;

$guest = trim($_GET['to'] ?? 'Tamu Undangan');
$woman = $people['wanita'] ?? [];
$man = $people['pria'] ?? [];
$wnick = $woman['nama_panggilan'] ?? ($settings['nama_wanita'] ?? 'Putri');
$mnick = $man['nama_panggilan'] ?? ($settings['nama_pria'] ?? 'Andika');
$wfull = $woman['nama_lengkap'] ?? 'Putri Cantika Sari';
$mfull = $man['nama_lengkap'] ?? 'Putra Andika Pratama';
$date = !empty($settings['tanggal']) ? new DateTime($settings['tanggal']) : new DateTime('2027-12-28');
$dateDots = $date->format('d . m . Y');
$dateCompact = $date->format('d.m.Y');

$html = file_get_contents(__DIR__.'/template/art55-template.html');
$replace = [
    'Putri Cantika Sari' => htmlspecialchars($wfull, ENT_QUOTES, 'UTF-8'),
    'Putra Andika Pratama' => htmlspecialchars($mfull, ENT_QUOTES, 'UTF-8'),
    'Putri &amp; Andika' => htmlspecialchars($wnick, ENT_QUOTES, 'UTF-8').' &amp; '.htmlspecialchars($mnick, ENT_QUOTES, 'UTF-8'),
    'Putri<br>&amp;<br>Andika' => htmlspecialchars($wnick, ENT_QUOTES, 'UTF-8').'<br>&amp;<br>'.htmlspecialchars($mnick, ENT_QUOTES, 'UTF-8'),
    'Putri<br><span style="color:#333;font-family:aston-script;font-size:24px;font-weight:normal">&amp;</span><br>Andika' => htmlspecialchars($wnick, ENT_QUOTES, 'UTF-8').'<br><span style="color:#333;font-family:aston-script;font-size:24px;font-weight:normal">&amp;</span><br>'.htmlspecialchars($mnick, ENT_QUOTES, 'UTF-8'),
    '<h2 class="elementor-heading-title elementor-size-default">Tamu Undangan</h2>' => '<h2 class="elementor-heading-title elementor-size-default">'.htmlspecialchars($guest, ENT_QUOTES, 'UTF-8').'</h2>',
    '<p class="elementor-heading-title elementor-size-default">Tamu Undangan</p>' => '<p class="elementor-heading-title elementor-size-default">'.htmlspecialchars($guest, ENT_QUOTES, 'UTF-8').'</p>',
    '28 . 12 . 2027' => $dateDots,
    '28.12.2027' => $dateCompact,
];
$html = strtr($html, $replace);

// Keep the original Art 55 markup/assets. Only neutralize the original WordPress RSVP submission;
// our local RSVP endpoint is attached through a small compatibility script.
$bridge = <<<'HTML'
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Preserve all original visual/animation assets. Local PHP owns only data submission.
  const forms = Array.from(document.querySelectorAll('form'));
  forms.forEach(form => {
    const text = (form.innerText || '').toLowerCase();
    if (!text.includes('kehadiran') && !text.includes('ucapan')) return;
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      const fd = new FormData(form);
      const payload = new FormData();
      const get = (...names) => { for (const n of names) { const v=fd.get(n); if(v) return v; } return ''; };
      payload.set('nama', get('nama','name','form_fields[nama]','form_fields[name]') || 'Tamu');
      payload.set('ucapan', get('ucapan','message','form_fields[ucapan]','form_fields[message]') || '-');
      payload.set('kehadiran', get('kehadiran','attendance','form_fields[kehadiran]') || 'belum_konfirmasi');
      payload.set('jumlah_tamu', get('jumlah_tamu','guest','form_fields[jumlah_tamu]') || '1');
      try {
        const res = await fetch('api/rsvp.php', {method:'POST', body:payload});
        if(!res.ok) throw new Error('HTTP '+res.status);
        alert('Terima kasih, konfirmasi Anda sudah tersimpan.');
        form.reset();
      } catch(err) { alert('Konfirmasi belum dapat disimpan. Silakan coba lagi.'); }
    }, true);
  });
});
</script>
HTML;
$html = str_ireplace('</body>', $bridge."\n</body>", $html);
echo $html;
