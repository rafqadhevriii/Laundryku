<?php
// Restricted launcher: no arbitrary Artisan command, database argument, reset, wipe or rollback.
try{
    $command=$argv[1]??'';
    if(!in_array($command,['setup','advanced','status','serve'],true))throw new \RuntimeException('Gunakan: php scripts/blackbox.php setup|advanced|status|serve [--port=8086] [--host=127.0.0.1]');
    $host='127.0.0.1';$port='8086';
    foreach(array_slice($argv,2) as $arg){
        if($command==='serve'&&preg_match('/^--port=([0-9]{4,5})$/',$arg,$m)&&(int)$m[1]>=1024&&(int)$m[1]<=65535){$port=$m[1];continue;}
        if($command==='serve'&&in_array($arg,['--host=127.0.0.1','--host=0.0.0.0'],true)){$host=substr($arg,7);continue;}
        throw new \RuntimeException('Argumen tidak diizinkan; tidak ada opsi database/reset/force.');
    }
    $app=require __DIR__.'/blackbox-bootstrap.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $setup=new \App\Support\Blackbox\Setup($app);
    if($command==='setup')$result=$setup->initial();
    elseif($command==='advanced')$result=$setup->advanced();
    elseif($command==='status')$result=$setup->status();
    else{
        echo "Mode BLACKBOX: laundryku_blackbox; http://{$host}:{$port}; hasil manual belum diisi.\n";
        $process=new \Symfony\Component\Process\Process([PHP_BINARY,'-S',"{$host}:{$port}",'-t',$app->publicPath(),__DIR__.'/blackbox-router.php'],$app->publicPath());
        $process->setTimeout(null);exit($process->run(fn($type,$buffer)=>print($buffer)));
    }
    echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR).PHP_EOL;
}catch(\Throwable $e){
    // Do not print SQL, connection secrets or exception traces to the console.
    $safe=$e instanceof \App\Support\Blackbox\SafetyException||get_class($e)===\RuntimeException::class;
    fwrite(STDERR,($safe?$e->getMessage():'Mode blackbox gagal; periksa database khusus, hak schema dan storage. Tidak ada reset otomatis.').PHP_EOL);exit(1);
}
