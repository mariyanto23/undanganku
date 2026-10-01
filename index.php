<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
session_start();
require __DIR__ . '/config/database.php';
require __DIR__ . '/functions/helper.php';

$pdo = db();
$settings = $pdo->query('SELECT * FROM settings WHERE id=1 LIMIT 1')->fetch() ?: [];
if (($settings['status'] ?? 'published') === 'draft' && empty($_SESSION['admin_id'])) {
    http_response_code(404);
    exit('Undangan belum dipublikasikan.');
}

$people = [];
foreach ($pdo->query('SELECT * FROM mempelai') as $row) $people[$row['tipe']] = $row;
$events = [];
foreach ($pdo->query('SELECT * FROM acara') as $row) $events[$row['jenis']] = $row;
$stories = $pdo->query('SELECT tanggal_label, judul, cerita FROM love_story ORDER BY urutan, id')->fetchAll();
$accounts = $pdo->query('SELECT bank, nomor_rekening, atas_nama FROM rekening ORDER BY urutan, id')->fetchAll();
$gallery = $pdo->query('SELECT file_path, caption FROM gallery ORDER BY urutan, id')->fetchAll();
$guestbook = $pdo->query('SELECT nama, ucapan, kehadiran, jumlah_tamu, created_at FROM guestbook ORDER BY id DESC LIMIT 100')->fetchAll();

$guest = trim($_GET['to'] ?? 'Tamu Undangan');
$woman = $people['wanita'] ?? [];
$man = $people['pria'] ?? [];
$wnick = $woman['nama_panggilan'] ?? ($settings['nama_wanita'] ?? 'Putri');
$mnick = $man['nama_panggilan'] ?? ($settings['nama_pria'] ?? 'Andika');
$wfull = $woman['nama_lengkap'] ?? 'Putri Cantika Sari';
$mfull = $man['nama_lengkap'] ?? 'Putra Andika Pratama';
$title = $settings['judul'] ?? 'The Wedding Of';
$date = new DateTime($settings['tanggal'] ?? '2027-12-28');
$dateDots = $date->format('d . m . Y');
$dateCompact = $date->format('d.m.Y');
$countdownTimestamp = (clone $date)->setTime(0, 0)->getTimestamp();
$calendarUrl = 'https://www.google.com/calendar/render?action=TEMPLATE&text=' . rawurlencode($title . ' ' . $wnick . ' & ' . $mnick) . '&dates=' . $date->format('Ymd') . 'T010000Z/' . (clone $date)->modify('+1 day')->format('Ymd') . 'T010000Z';
$parentsWoman = 'Bapak ' . ($woman['ayah'] ?? '') . ' dan Ibu ' . ($woman['ibu'] ?? '');
$parentsMan = 'Bapak ' . ($man['ayah'] ?? '') . ' dan Ibu ' . ($man['ibu'] ?? '');
$cover = public_image($settings['cover_image'] ?? '');
$womanPhoto = public_image($woman['foto'] ?? '');
$manPhoto = public_image($man['foto'] ?? '');
$months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

$html = file_get_contents(__DIR__ . '/template/art55-template.html');
$html = strtr($html, [
    'Putri Cantika Sari' => e($wfull),
    'Putra Andika Pratama' => e($mfull),
    'Putri &amp; Andika' => e($wnick) . ' &amp; ' . e($mnick),
    'Putri<br>&amp;<br>Andika' => e($wnick) . '<br>&amp;<br>' . e($mnick),
    'Putri<br><span style="color:#333;font-family:aston-script;font-size:24px;font-weight:normal">&amp;</span><br>Andika' => e($wnick) . '<br><span style="color:#333;font-family:aston-script;font-size:24px;font-weight:normal">&amp;</span><br>' . e($mnick),
    '28 . 12 . 2027' => $dateDots,
    '28.12.2027' => $dateCompact,
    'Bapak Abdul Rozak dan Ibu Adelia Marni' => e($parentsWoman),
    'Bapak Deni Bastian dan Ibu Aisha Dania' => e($parentsMan),
    'Q.S Ar-Rum : 21' => e($settings['quote_source'] ?? 'Q.S Ar-Rum : 21'),
    'Maha Suci Allah yang telah menciptakan makhluk-Nya berpasang-pasangan. Ya Allah semoga ridho-Mu tercurah mengiringi pernikahan kami.' => nl2br(e($settings['quote'] ?? '')),
    'Kami dengan hormat menganjurkan tamu kami untuk mengenakan warna-warna ini untuk hari istimewa kami.' => nl2br(e($settings['dresscode_text'] ?? '')),
    '08:00 WIB' => e(time_range($events['akad']['jam_mulai'] ?? '08:00:00', $events['akad']['jam_selesai'] ?? null)),
    '09:00 - 13:00 WIB' => e(time_range($events['resepsi']['jam_mulai'] ?? '09:00:00', $events['resepsi']['jam_selesai'] ?? '13:00:00')),
    '<h2 class="elementor-heading-title elementor-size-default">Tamu Undangan</h2>' => '<h2 class="elementor-heading-title elementor-size-default">' . e($guest) . '</h2>',
    '<p class="elementor-heading-title elementor-size-default">Tamu Undangan</p>' => '<p class="elementor-heading-title elementor-size-default">' . e($guest) . '</p>',
]);
$html = str_replace('data-date="1829955600"', 'data-date="' . $countdownTimestamp . '"', $html);
$html = preg_replace('~href="https://www\.google\.com/calendar/render[^\"]*"~', 'href="' . e($calendarUrl) . '"', $html, 1);

foreach (['akad', 'resepsi'] as $jenis) {
    if (!empty($events[$jenis])) {
        $html = str_replace('Menara 165', e($events[$jenis]['nama_lokasi'] ?? ''), $html);
        $html = str_replace('Jl. TB Simatupang Jakarta Selatan', nl2br(e($events[$jenis]['alamat'] ?? '')), $html);
    }
}
$maps = array_values(array_filter([$events['akad']['maps_url'] ?? null, $events['resepsi']['maps_url'] ?? null]));
$mapIndex = 0;
$html = preg_replace_callback('~href="https://maps\.app\.goo\.gl/TsZCeupoF4p6bksT6"~', static function () use (&$mapIndex, $maps) {
    $url = $maps[$mapIndex] ?? end($maps) ?: '#';
    $mapIndex++;
    return 'href="' . e($url) . '"';
}, $html);
if ($cover) {
    $html = str_replace('https://the.invisimple.id/wp-content/uploads/2026/06/Cover-Jawa-Biru-.jpg', e($cover), $html);
    $html = str_replace('https://the.invisimple.id/wp-content/uploads/2026/06/Fallback-Jawa-Biru-.jpg', e($cover), $html);
}
if ($womanPhoto) $html = preg_replace('~https://the\.invisimple\.id/wp-content/uploads/2024/10/05\.png~', e($womanPhoto), $html, 1);
if ($manPhoto) $html = preg_replace('~https://the\.invisimple\.id/wp-content/uploads/2024/10/05\.png~', e($manPhoto), $html, 1);

$data = [
    'stories' => $stories,
    'accounts' => $accounts,
    'gallery' => array_map(static fn($item) => ['url' => public_image($item['file_path']), 'caption' => $item['caption']], $gallery),
    'guestbook' => $guestbook,
    'events' => array_combine(['akad', 'resepsi'], array_map(static function (string $jenis) use ($events, $months): array {
        $event = $events[$jenis] ?? [];
        $eventDate = new DateTime($event['tanggal'] ?? '2027-12-28');
        return [
            'day' => day_id($eventDate->format('Y-m-d')),
            'number' => $eventDate->format('d'),
            'month' => $months[(int) $eventDate->format('n') - 1],
            'year' => $eventDate->format('Y'),
            'time' => time_range($event['jam_mulai'] ?? null, $event['jam_selesai'] ?? null),
            'location' => $event['nama_lokasi'] ?? '',
            'address' => $event['alamat'] ?? '',
            'maps' => $event['maps_url'] ?? '',
        ];
    }, ['akad', 'resepsi'])),
    'liveUrl' => trim($settings['live_url'] ?? ''),
    'musicUrl' => public_image($settings['music_file'] ?? ''),
    'rsvpUrl' => base_url() . '/api/rsvp.php',
];
$dataJson = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);

$bridge = <<<'HTML'
<style>
.saic-container-comments.local-guestbook{display:block!important;max-height:420px;overflow-y:auto;margin:24px 0 0!important;padding:0 4px 4px!important;scrollbar-width:thin;scrollbar-color:#8290aa transparent}.saic-container-comments.local-guestbook::-webkit-scrollbar{width:6px}.saic-container-comments.local-guestbook::-webkit-scrollbar-thumb{border-radius:6px;background:#8290aa}.saic-container-comments.local-guestbook .local-guestbook-item{list-style:none;margin:0 0 14px!important;padding:18px 16px!important;border:0!important;border-radius:8px;background:#fff;box-shadow:none}.saic-container-comments.local-guestbook .local-guestbook-item strong{display:block;color:#14386e;font-family:Georgia,serif;font-size:16px;font-weight:700;line-height:1.3}.saic-container-comments.local-guestbook .local-guestbook-item p{margin:4px 0 0!important;color:#333;font-family:Georgia,serif;font-size:15px;line-height:1.45;white-space:pre-line}
.local-list{display:grid;gap:14px;margin:24px auto;max-width:620px}.local-card{background:#fff;border:1px solid #d9e0e8;padding:20px;text-align:center}.local-card strong{color:#032262;display:block;font-size:20px}.local-number{font-size:24px;letter-spacing:.08em;margin:9px 0}.local-gallery{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.local-gallery figure{margin:0}.local-gallery img{display:block;width:100%;aspect-ratio:1;object-fit:cover}.local-gallery figcaption{font-size:12px;padding:6px;text-align:center}.local-rsvp-status{margin-top:12px;text-align:center}.local-rsvp-status.error{color:#b42318}.local-rsvp-status.success{color:#167044}.local-guestbook{display:grid;gap:10px;margin-top:24px}.local-guestbook-item{border-top:1px solid #e4e7eb;padding:12px 0}.local-guestbook-item p{margin:7px 0;white-space:pre-line}@media(max-width:640px){.local-gallery{grid-template-columns:repeat(2,1fr)}}
</style>
<script>
(function(){
const invitation=__INVITATION_DATA__,esc=v=>String(v??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));
const label=v=>({hadir:'Hadir',tidak_hadir:'Tidak hadir',belum_konfirmasi:'Belum konfirmasi'}[v]||'Belum konfirmasi');
function copyText(value){const fallback=()=>{const input=document.createElement('textarea');input.value=value;input.setAttribute('readonly','');input.style.cssText='position:fixed;opacity:0;pointer-events:none';document.body.appendChild(input);input.select();const copied=document.execCommand('copy');input.remove();if(!copied)throw new Error('copy failed')};if(navigator.clipboard&&window.isSecureContext)return navigator.clipboard.writeText(value).catch(fallback);return Promise.resolve().then(fallback)}
function comments(){const c=document.querySelector('.saic-container-comments');if(!c)return;c.innerHTML=invitation.guestbook.map(x=>'<li class="local-guestbook-item"><strong>'+esc(x.nama)+'</strong><p>'+esc(x.ucapan)+'</p></li>').join('');c.classList.add('local-guestbook');const w=c.closest('.saic-wrap-comments');if(w)w.style.display='block'}
function stories(){const c=document.querySelector('.jet-timeline-list');if(!c||!invitation.stories.length)return;c.innerHTML=invitation.stories.map((x,i)=>'<div class="jet-timeline-item jet-timeline-item--animated"><div class="timeline-item__card"><div class="timeline-item__card-inner"><div class="timeline-item__card-content"><h5 class="timeline-item__card-title">'+esc(x.tanggal_label)+'</h5><div class="timeline-item__card-desc">'+(x.judul?'<strong>'+esc(x.judul)+'</strong><br>':'')+esc(x.cerita)+'</div></div></div><div class="timeline-item__card-arrow"></div></div><div class="timeline-item__point"><div class="timeline-item__point-content timeline-item__point-content--icon"><span class="jet-elements-icon"><i class="fas fa-heart"></i></span></div></div></div>').join('')}
function accounts(){
  const cards=['4c12931','4506cb1','1abf2d7'].map(id=>document.querySelector(`[data-id="${id}"]`)).filter(Boolean);
  if(!cards.length)return;
  const logos={BCA:'https://the.invisimple.id/wp-content/uploads/2024/10/bca.png',BRI:'https://the.invisimple.id/wp-content/uploads/2024/10/bri.png','ALLIANCE BANK':'https://the.invisimple.id/wp-content/uploads/2025/10/alliance-bank.png'};
  invitation.accounts.forEach((x,index)=>{
    let card=cards[index];
    if(!card){card=cards[0].cloneNode(true);cards[cards.length-1].insertAdjacentElement('afterend',card);cards.push(card)}
    card.style.display='';
    const text=card.querySelectorAll('p.elementor-heading-title');
    if(text[1])text[1].textContent=x.nomor_rekening;
    if(text[3])text[3].textContent=x.atas_nama;
    const logo=card.querySelector('img[width="153"]'),bank=String(x.bank||'').trim(),src=logos[bank.toUpperCase()];
    if(logo&&src){logo.src=src;logo.alt=bank;logo.removeAttribute('srcset');logo.style.display=''}
    else if(logo){logo.style.display='none';const wrap=logo.parentElement;if(wrap){wrap.textContent=bank;wrap.style.cssText='padding:10px;color:#17356f;font-weight:700;background:#fff;border-radius:8px'}}
    const button=card.querySelector('button[data-clipboard-text]');
    if(button){
      button.dataset.clipboardText=x.nomor_rekening;
      button.addEventListener('click',async event=>{
        event.preventDefault();
        event.stopImmediatePropagation();
        const buttonLabel=button.querySelector('.elementor-button-text');
        try{await copyText(x.nomor_rekening);if(buttonLabel){buttonLabel.textContent='Tersalin';setTimeout(()=>buttonLabel.textContent='Salin',1500)}}
        catch(_){if(buttonLabel)buttonLabel.textContent='Gagal disalin'}
      },true);
    }
  });
  cards.forEach((card,index)=>{if(index>=invitation.accounts.length)card.style.display='none'});
}
function gallery(){const a=document.querySelector('#show_amplop');if(!a||!invitation.gallery.length)return;const s=document.createElement('section');s.className='elementor-section elementor-inner-section';s.innerHTML='<div class="elementor-container"><div class="elementor-column elementor-col-100"><div class="elementor-widget-wrap"><h2 class="elementor-heading-title elementor-size-default">Gallery</h2><div class="local-gallery">'+invitation.gallery.map(x=>'<figure><img src="'+esc(x.url)+'" alt="'+esc(x.caption||'Foto pernikahan')+'"><figcaption>'+esc(x.caption||'')+'</figcaption></figure>').join('')+'</div></div></div></div>';a.insertAdjacentElement('beforebegin',s)}
function events(){const ids={akad:['b17d2c6','6c857a7','bbd07cf','839c710','77c884f','c75cc47'],resepsi:['750b47d','837124c','7a64e22','28d7359','dd918d3','27179d2']};Object.entries(ids).forEach(([type,id])=>{const x=invitation.events[type];if(!x)return;const q=n=>document.querySelector('[data-id="'+n+'"]');const set=(n,v)=>{const el=q(n);if(el)el.querySelector('.elementor-heading-title').textContent=v};set(id[0],x.day);set(id[1],x.number);const month=q(id[2]);if(month){const text=month.querySelectorAll('.elementor-icon-list-text');if(text[0])text[0].textContent=x.month;if(text[1])text[1].textContent=x.year}const time=q(id[3]);if(time)time.querySelector('.elementor-icon-list-text').textContent=x.time;const place=q(id[4]);if(place)place.querySelector('.elementor-heading-title').innerHTML='<b>'+esc(x.location)+'</b><br>'+esc(x.address);const map=q(id[5]);if(map&&x.maps)map.querySelector('a').href=x.maps})}
document.addEventListener('DOMContentLoaded',()=>{stories();accounts();gallery();events();comments();const live=document.querySelector('[data-id="a9913e2"] a');if(live){if(invitation.liveUrl)live.href=invitation.liveUrl;else live.closest('[data-id="a9913e2"]').style.display='none'}if(invitation.musicUrl){const audio=new Audio(invitation.musicUrl);audio.loop=true;const open=document.querySelector('#btn_open a');if(open)open.addEventListener('click',()=>audio.play().catch(()=>{}),{once:true})}});
document.addEventListener('submit',async e=>{const f=e.target;if(!f.matches('#commentform-6854'))return;e.preventDefault();e.stopImmediatePropagation();const fd=new FormData(f),body=new FormData();body.set('nama',fd.get('author')||'Tamu');body.set('ucapan',fd.get('comment')||'');body.set('jumlah_tamu',fd.get('guest')||'1');body.set('website','');const a=fd.get('attendance');body.set('kehadiran',a==='present'?'hadir':a==='notpresent'?'tidak_hadir':'belum_konfirmasi');let status=f.querySelector('.local-rsvp-status');if(!status){status=document.createElement('div');status.className='local-rsvp-status';f.appendChild(status)}const button=f.querySelector('[type="submit"]');button.disabled=true;status.className='local-rsvp-status';status.textContent='Mengirim...';try{const r=await fetch(invitation.rsvpUrl,{method:'POST',body,headers:{Accept:'application/json'}}),out=await r.json();if(!r.ok)throw Error(out.message);invitation.guestbook.unshift(out.entry);comments();f.reset();status.className='local-rsvp-status success';status.textContent=out.message}catch(err){status.className='local-rsvp-status error';status.textContent=err.message||'Konfirmasi belum dapat disimpan.'}finally{button.disabled=false}},true);
})();
</script>
HTML;
$bridge = str_replace('__INVITATION_DATA__', $dataJson ?: '{}', $bridge);
$html = str_ireplace('</body>', $bridge . "\n</body>", $html);
echo $html;
