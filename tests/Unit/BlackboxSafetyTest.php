<?php
namespace Tests\Unit;

use App\Support\Blackbox\{Environment,Guard,SafetyException};
use PHPUnit\Framework\Attributes\{DataProvider,RunInSeparateProcess,PreserveGlobalState};
use PHPUnit\Framework\TestCase;

class BlackboxSafetyTest extends TestCase
{
    private function valid(): array {return ['APP_ENV'=>'blackbox','DB_CONNECTION'=>'blackbox','DB_DATABASE'=>'laundryku_blackbox','DB_URL'=>'','BLACKBOX_DB_USERNAME'=>'bb_only','BLACKBOX_ACCOUNT_PASSWORD'=>'synthetic-test-only'];}
    public function test_manual_configuration_accepts_only_the_exact_manual_database(): void
    {
        Environment::validate($this->valid());$this->addToAssertionCount(1);
        foreach(['laundryku','laundryku_testing','laundryku_blackbox_copy','Laundryku_blackbox',''] as $db){
            try{Environment::validate([...$this->valid(),'DB_DATABASE'=>$db]);$this->fail('Protected database accepted');}catch(SafetyException $e){$this->assertStringContainsString('laundryku_blackbox',$e->getMessage());}
        }
    }
    public static function unsafeEnvironment(): array {return [
        [['APP_URL'=>'https://production.example.test']],[['APP_ENV'=>'production']],[['DB_CONNECTION'=>'mysql']],[['DB_URL'=>'mysql://localhost/laundryku']],
        [['BLACKBOX_DB_USERNAME'=>'root']],[['BLACKBOX_ACCOUNT_PASSWORD'=>'short']],[['APP_CONFIG_CACHE'=>'bootstrap/cache/config.php']],
        [['DB_HOST'=>'production']],[['BLACKBOX_DB_PORT'=>'3306;DROP']],[['APP_KEY'=>'shared-key']],
    ];}
    #[DataProvider('unsafeEnvironment')]
    public function test_environment_overrides_fail_before_boot_or_database_access(array $override): void
    {
        $this->expectException(SafetyException::class);Environment::validate([...$this->valid(),...$override]);
    }
    public function test_connection_guard_rejects_named_protected_databases_urls_and_alternate_connections(): void
    {
        $connection=['driver'=>'mysql','database'=>'laundryku_blackbox'];Guard::configuration('blackbox','blackbox',$connection);$this->addToAssertionCount(1);
        foreach([['database'=>'laundryku'],['database'=>'laundryku_testing'],['url'=>'mysql://localhost/laundryku'],['write'=>['database'=>'laundryku']],['read'=>['database'=>'laundryku_testing']],['options'=>[1=>'USE laundryku']],['prefix'=>'main.']] as $override){
            try{Guard::configuration('blackbox','blackbox',[...$connection,...$override]);$this->fail('Unsafe connection accepted');}catch(SafetyException $e){$this->assertStringContainsString('ditolak',$e->getMessage());}
        }
        foreach([['production','blackbox'],['testing','mysql'],['blackbox','mysql']] as [$env,$name]){
            try{Guard::configuration($env,$name,$connection);$this->fail('Unsafe default accepted');}catch(SafetyException $e){$this->assertStringContainsString('ditolak',$e->getMessage());}
        }
    }
    public function test_database_grants_must_be_literal_and_exclusive_to_blackbox(): void
    {
        Guard::grants(["GRANT USAGE ON *.* TO 'bb'@'localhost'", "GRANT ALL PRIVILEGES ON `laundryku\\_blackbox`.* TO 'bb'@'localhost'"]);$this->addToAssertionCount(1);
        foreach([
            ['GRANT ALL PRIVILEGES ON *.* TO bb'],['GRANT ALL PRIVILEGES ON `laundryku`.* TO bb'],
            ['GRANT ALL PRIVILEGES ON `laundryku_testing`.* TO bb'],['GRANT ALL PRIVILEGES ON `laundryku_blackbox`.* TO bb'],
            ['GRANT ALL PRIVILEGES ON `laundryku\\_blackbox`.* TO bb WITH GRANT OPTION'],['GRANT dangerous_role TO bb'],[],
            ['GRANT ALL PRIVILEGES ON `laundryku\\_blackbox`.* TO bb','GRANT SELECT ON `laundryku`.* TO bb'],
        ] as $grants){try{Guard::grants($grants);$this->fail('Unsafe grants accepted');}catch(SafetyException $e){$this->assertNotEmpty($e->getMessage());}}
    }
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_launcher_storage_key_and_cache_do_not_share_the_main_environment(): void
    {
        $base=sys_get_temp_dir().'/laundryku-blackbox-'.bin2hex(random_bytes(6));mkdir($base,0700,true);
        file_put_contents($base.'/.env',"APP_ENV=production\nDB_DATABASE=laundryku\n");
        file_put_contents($base.'/.env.blackbox',"APP_ENV=blackbox\nDB_CONNECTION=blackbox\nDB_DATABASE=laundryku_blackbox\nBLACKBOX_DB_USERNAME=bb_only\nBLACKBOX_ACCOUNT_PASSWORD=synthetic-test-only\n");
        if(DIRECTORY_SEPARATOR==='/'&&function_exists('symlink')){
            mkdir($base.'/storage');mkdir($base.'/shared');symlink($base.'/shared',$base.'/storage/blackbox');
            try{Environment::prepare($base);$this->fail('Shared storage symlink accepted');}catch(SafetyException $e){$this->assertStringContainsString('symlink',$e->getMessage());}
            $this->assertFileDoesNotExist($base.'/shared/app.key');unlink($base.'/storage/blackbox');
        }
        putenv('DB_URL=mysql://localhost/laundryku');
        try{
            Environment::prepare($base);$key=file_get_contents($base.'/storage/blackbox/app.key');
            $this->assertSame('blackbox',getenv('APP_ENV'));$this->assertSame('',getenv('DB_URL'));
            $this->assertSame($base.'/storage/blackbox',getenv('LARAVEL_STORAGE_PATH'));
            $this->assertSame('laundryku_blackbox_session',getenv('SESSION_COOKIE'));
            $this->assertSame($base.'/storage/blackbox/bootstrap/config-disabled.php',getenv('APP_CONFIG_CACHE'));
            Environment::prepare($base);$this->assertSame($key,file_get_contents($base.'/storage/blackbox/app.key'));
            $this->assertStringContainsString('DB_DATABASE=laundryku',file_get_contents($base.'/.env'));
            file_put_contents(getenv('APP_CONFIG_CACHE'),'cache-sentinel');
            try{Environment::prepare($base);$this->fail('Unexpected cache accepted');}catch(SafetyException $e){$this->assertStringContainsString('Cache',$e->getMessage());}
            $this->assertSame('cache-sentinel',file_get_contents($base.'/storage/blackbox/bootstrap/config-disabled.php'));
        }finally{
            $iterator=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base,\FilesystemIterator::SKIP_DOTS),\RecursiveIteratorIterator::CHILD_FIRST);
            foreach($iterator as $file){if($file->isDir())rmdir($file->getPathname());else unlink($file->getPathname());}rmdir($base);
        }
    }
    public function test_manual_result_template_has_all_52_cases_and_no_outcomes(): void
    {
        $file=fopen(dirname(__DIR__,2).'/docs/blackbox/results-template.csv','r');$header=fgetcsv($file,0,',','"','');$ids=[];
        while(($values=fgetcsv($file,0,',','"',''))!==false){$row=array_combine($header,$values);$ids[]=$row['id'];$this->assertSame('Belum diuji manual',$row['status']);foreach(['hasil_aktual','tanggal_jam','penguji','screenshot'] as $key)$this->assertSame('',$row[$key]);}
        fclose($file);sort($ids);$this->assertSame(array_map(fn($n)=>sprintf('BB-%02d',$n),range(1,52)),$ids);
    }

}
