<?php
session_start();require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../functions/helper.php';
if($_SERVER['REQUEST_METHOD']!=='POST')redirect('/');verify_csrf();
$nama=trim($_POST['nama']??'');$ucapan=trim($_POST['ucapan']??'');$hadir=$_POST['kehadiran']??'belum_konfirmasi';$jumlah=max(1,min(10,(int)($_POST['jumlah_tamu']??1)));
if($nama===''||mb_strlen($ucapan)<2)exit('Data tidak valid.');if(!in_array($hadir,['hadir','tidak_hadir','belum_konfirmasi'],true))$hadir='belum_konfirmasi';
$q=$pdo->prepare('INSERT INTO guestbook(nama,ucapan,kehadiran,jumlah_tamu) VALUES(?,?,?,?)');$q->execute([$nama,$ucapan,$hadir,$jumlah]);
redirect(($_SERVER['HTTP_REFERER']??'/').'#content');
