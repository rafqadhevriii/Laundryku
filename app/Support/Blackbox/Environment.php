<?php
namespace App\Support\Blackbox;

use Dotenv\Dotenv;

/** Only this launcher reads .env.blackbox; ordinary .env and shared caches are never loaded. */
class Environment
{
    public static function validate(array $values): void
    {
        $allowed=['APP_ENV','APP_URL','DB_CONNECTION','DB_DATABASE','DB_URL','BLACKBOX_DB_HOST','BLACKBOX_DB_PORT','BLACKBOX_DB_USERNAME','BLACKBOX_DB_PASSWORD','BLACKBOX_DB_SOCKET','BLACKBOX_ACCOUNT_PASSWORD'];
        if(array_diff(array_keys($values),$allowed))throw new SafetyException('Konfigurasi blackbox memuat key yang tidak diizinkan. Gunakan .env.blackbox.example.');
        if(($values['APP_ENV']??'')!=='blackbox'||($values['DB_CONNECTION']??'')!=='blackbox'||($values['DB_DATABASE']??'')!=='laundryku_blackbox'||!empty($values['DB_URL']))throw new SafetyException('Hanya APP_ENV=blackbox, connection blackbox dan laundryku_blackbox tanpa DB_URL yang diizinkan.');
        if(!preg_match('~^https?://(127\.0\.0\.1|localhost)(:[0-9]{2,5})?$~',$values['APP_URL']??'http://127.0.0.1:8086'))throw new SafetyException('APP_URL blackbox harus localhost/127.0.0.1, bukan aplikasi utama.');
        if(empty($values['BLACKBOX_DB_USERNAME'])||strtolower($values['BLACKBOX_DB_USERNAME'])==='root')throw new SafetyException('Gunakan akun database khusus blackbox, bukan root.');
        if(strlen($values['BLACKBOX_ACCOUNT_PASSWORD']??'')<12)throw new SafetyException('Isi BLACKBOX_ACCOUNT_PASSWORD minimal 12 karakter untuk akun sintetis.');
        if(!preg_match('/^[0-9]{1,5}$/',$values['BLACKBOX_DB_PORT']??'3306')||(int)($values['BLACKBOX_DB_PORT']??3306)<1||(int)($values['BLACKBOX_DB_PORT']??3306)>65535)throw new SafetyException('Port database tidak valid.');
    }

    public static function prepare(string $base): void
    {
        $file=$base.'/.env.blackbox';
        if(!is_file($file))throw new SafetyException('Salin .env.blackbox.example ke .env.blackbox dan isi kredensial khusus terlebih dahulu.');
        $values=Dotenv::parse(file_get_contents($file));
        self::validate($values);
        $storage=$base.'/storage/blackbox';
        if(is_link($storage))throw new SafetyException('Storage blackbox tidak boleh berupa symlink ke lingkungan lain.');
        foreach(['','framework/cache/data','framework/sessions','framework/views','logs','bootstrap','config','evidence'] as $dir){
            $path=$storage.($dir?'/'.$dir:'');
            if(!is_dir($path)&&!mkdir($path,0700,true)&&!is_dir($path))throw new SafetyException('Tidak dapat membuat storage blackbox.');
            if(realpath($path)!==realpath($storage)&&!str_starts_with(realpath($path),realpath($storage).DIRECTORY_SEPARATOR))throw new SafetyException('Path storage blackbox keluar dari direktori terisolasi.');
        }
        $keyFile=$storage.'/app.key';
        if(is_link($keyFile))throw new SafetyException('Kunci blackbox tidak boleh berupa symlink.');
        if(!is_file($keyFile)){
            $handle=@fopen($keyFile,'x');
            if($handle){fwrite($handle,'base64:'.base64_encode(random_bytes(32)));fclose($handle);chmod($keyFile,0600);}
        }
        $key=trim((string)file_get_contents($keyFile));
        if(!str_starts_with($key,'base64:')||strlen((string)base64_decode(substr($key,7),true))!==32)throw new SafetyException('Kunci lokal blackbox tidak valid.');
        $fixed=[
            'APP_ENV'=>'blackbox','APP_DEBUG'=>'false','APP_NAME'=>'Laundryku Black Box','APP_KEY'=>$key,'APP_URL'=>$values['APP_URL']??'http://127.0.0.1:8086',
            'DB_CONNECTION'=>'blackbox','DB_DATABASE'=>'laundryku_blackbox','DB_URL'=>'',
            'APP_CONFIG_CACHE'=>$storage.'/bootstrap/config-disabled.php',
            'APP_SERVICES_CACHE'=>$storage.'/bootstrap/services.php','APP_PACKAGES_CACHE'=>$storage.'/bootstrap/packages.php',
            'APP_ROUTES_CACHE'=>$storage.'/bootstrap/routes-disabled.php','APP_EVENTS_CACHE'=>$storage.'/bootstrap/events.php',
            'LARAVEL_STORAGE_PATH'=>$storage,'VIEW_COMPILED_PATH'=>$storage.'/framework/views',
            'SESSION_DRIVER'=>'file','SESSION_COOKIE'=>'laundryku_blackbox_session','SESSION_DOMAIN'=>'null',
            'SESSION_PATH'=>'/','SESSION_SECURE_COOKIE'=>'false','SESSION_ENCRYPT'=>'false',
            'CACHE_STORE'=>'file','CACHE_PREFIX'=>'laundryku_blackbox','QUEUE_CONNECTION'=>'sync',
            'MAIL_MAILER'=>'log','LOG_CHANNEL'=>'single',
        ];
        foreach(['APP_CONFIG_CACHE','APP_ROUTES_CACHE'] as $name)if(is_file($fixed[$name]))throw new SafetyException('Cache konfigurasi/route blackbox tidak didukung. Hapus hanya cache terisolasi tersebut setelah ditinjau.');
        foreach(array_merge($values,$fixed) as $name=>$value){$_ENV[$name]=$_SERVER[$name]=(string)$value;putenv($name.'='.$value);}
    }
}
