<?php
function e($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function redirect($url){header('Location: '.$url);exit;}
function base_url(){ $c=require __DIR__.'/../config/config.php'; return rtrim($c['base_url'],'/'); }
function asset($path){return base_url().'/public/'.ltrim($path,'/');}
function upload_url($path){return base_url().'/uploads/'.ltrim($path,'/');}
function format_date_id($date){$m=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];$ts=strtotime($date);return date('d',$ts).' '. $m[(int)date('n',$ts)-1].' '.date('Y',$ts);}
function day_id($date){$d=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];return $d[(int)date('w',strtotime($date))];}
function time_range($start,$end=null){if(!$start)return ''; $a=date('H:i',strtotime($start)); return $end ? $a.' - '.date('H:i',strtotime($end)).' WIB' : $a.' WIB';}
function csrf_token(){if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(24));return $_SESSION['csrf'];}
function csrf_field(){return '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">';}
function verify_csrf(){if(!hash_equals($_SESSION['csrf']??'',$_POST['csrf']??'')){http_response_code(419);exit('CSRF token invalid');}}
function flash($key,$value=null){if($value!==null){$_SESSION['flash'][$key]=$value;return;} $v=$_SESSION['flash'][$key]??null;unset($_SESSION['flash'][$key]);return $v;}
function require_admin(){if(empty($_SESSION['admin_id']))redirect('/admin/login.php');}
function save_upload($field,$prefix='file'){if(empty($_FILES[$field]['name'])||$_FILES[$field]['error']!==UPLOAD_ERR_OK)return null;$f=$_FILES[$field];$allowed=['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp','mp3'=>'audio/mpeg'];$ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));if(!isset($allowed[$ext]))throw new RuntimeException('Format file tidak diizinkan.');if($f['size']>10*1024*1024)throw new RuntimeException('Ukuran file maksimal 10MB.');$cfg=require __DIR__.'/../config/config.php';if(!is_dir($cfg['upload_dir']))mkdir($cfg['upload_dir'],0755,true);$name=$prefix.'_'.bin2hex(random_bytes(8)).'.'.$ext;$dest=$cfg['upload_dir'].'/'.$name;if(!move_uploaded_file($f['tmp_name'],$dest))throw new RuntimeException('Upload gagal.');return $name;}
function public_image($path,$fallback=''){if(!$path)return $fallback;return str_starts_with($path,'http://')||str_starts_with($path,'https://')?$path:upload_url($path);}
