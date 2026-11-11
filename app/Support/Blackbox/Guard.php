<?php
namespace App\Support\Blackbox;

use Illuminate\Foundation\Application;

class Guard
{
    public static function configuration(string $environment,string $default,array $connection): void
    {
        if($environment!=='blackbox'||$default!=='blackbox'||($connection['driver']??'')!=='mysql'||($connection['database']??'')!=='laundryku_blackbox'||!empty($connection['url'])||!empty($connection['read'])||!empty($connection['write'])||!empty($connection['prefix'])||!empty($connection['options']))throw new SafetyException('Koneksi ditolak: mode ini hanya boleh memakai mysql laundryku_blackbox tanpa URL/override.');
    }

    /** Reject global rights, other schemas, roles, proxies and grant delegation. */
    public static function grants(array $grants): void
    {
        $schemaGrant=false;
        foreach($grants as $grant){
            if(preg_match('/\\bWITH GRANT OPTION\\b/i',$grant))throw new SafetyException('Akun database tidak boleh memiliki GRANT OPTION.');
            if(preg_match('/^GRANT USAGE ON \\*\\.\\* TO /i',$grant))continue;
            // Underscores MUST be escaped: MySQL schema grants treat them as wildcards.
            $target='`laundryku\\_blackbox`.*';
            $split=preg_split('/ ON /i',$grant,2);
            if(count($split)!==2||!str_starts_with($split[1],$target.' TO ')||!preg_match('/^GRANT [A-Z_, ()]+$/i',$split[0]))throw new SafetyException('Hak akses DB terlalu luas. Batasi akun hanya ke schema literal laundryku\\_blackbox, tanpa role/global grant.');
            $schemaGrant=true;
        }
        if(!$schemaGrant)throw new SafetyException('Hak schema laundryku_blackbox belum tersedia.');
    }

    public static function application(Application $app): void
    {
        $expected=realpath($app->basePath('storage/blackbox'));
        $actual=realpath($app->storagePath());
        if(!defined('LAUNDRYKU_BLACKBOX_LAUNCHER')||$expected===false||$actual===false||$actual!==$expected)throw new SafetyException('Gunakan php scripts/blackbox.php; storage/sesi utama tidak boleh dipakai.');
        self::configuration($app->environment(),config('database.default'),config('database.connections.blackbox',[]));
        $db=$app->make('db')->connection('blackbox');
        if(($db->selectOne('SELECT DATABASE() AS db_name')->db_name??'')!=='laundryku_blackbox')throw new SafetyException('Database aktual bukan laundryku_blackbox.');
        self::grants(array_map(fn($row)=>(string)array_values((array)$row)[0],$db->select('SHOW GRANTS FOR CURRENT_USER')));
    }
}
