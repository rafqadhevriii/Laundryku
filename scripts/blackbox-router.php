<?php
$public=realpath(dirname(__DIR__).'/public');
$uri=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$file=realpath($public.'/'.rawurldecode($uri));
if($file&&str_starts_with($file,$public.DIRECTORY_SEPARATOR)&&is_file($file)&&preg_match('~^/(build/|brand/|favicon\\.svg$|robots\\.txt$)~',$uri)&&pathinfo($file,PATHINFO_EXTENSION)!=='php')return false;
try{
    $app=require __DIR__.'/blackbox-bootstrap.php';
    $app->handleRequest(\Illuminate\Http\Request::capture());
}catch(\Throwable $e){http_response_code(503);echo 'Mode blackbox belum siap. Periksa konfigurasi/koneksi khusus dan jalankan status melalui launcher.';}
