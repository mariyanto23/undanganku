<?php
session_start();require_once __DIR__.'/helper.php';
function login_admin($id){$_SESSION['admin_id']=$id;session_regenerate_id(true);}
function logout_admin(){$_SESSION=[];if(ini_get('session.use_cookies')){ $p=session_get_cookie_params();setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']);}session_destroy();}
