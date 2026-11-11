<?php
namespace App\Support\Blackbox;

use App\Models\{Business,CashShift,Order,Payment,User};
use Illuminate\Foundation\Application;

class Setup
{
    public function __construct(private Application $app){}
    private function guarded(callable $action): array
    {
        Guard::application($this->app);
        $db=$this->app->make('db')->connection('blackbox');
        if((int)$db->selectOne("SELECT GET_LOCK('laundryku_blackbox_setup', 0) AS acquired")->acquired!==1)throw new SafetyException('Setup lain sedang berjalan. Tidak ada perubahan.');
        try{return $action($db);}finally{$db->selectOne("SELECT RELEASE_LOCK('laundryku_blackbox_setup')");}
    }
    public function initial(): array
    {
        return $this->guarded(function($db){
            if($db->getSchemaBuilder()->getTables())throw new SafetyException('Database blackbox tidak kosong. Setup ditolak; data/bukti sebelumnya tidak diubah. Tidak ada opsi reset.');
            // Only the existing forward migrations, on an EMPTY, verified, least-privilege schema.
            $exit=$this->app->make(\Illuminate\Contracts\Console\Kernel::class)->call('migrate',['--database'=>'blackbox','--force'=>true]);
            if($exit!==0)throw new SafetyException('Migrasi blackbox belum lengkap. Tidak ada reset/resume otomatis; tinjau schema khusus.');
            $db->transaction(fn()=>(new Fixtures)->initial());
            return $this->status();
        });
    }
    public function advanced(): array
    {
        return $this->guarded(function($db){
            if(!Business::where('code','BBOX-MANUAL-V1')->exists())throw new SafetyException('Jalankan setup awal pada database kosong terlebih dahulu.');
            if(Business::where('code','BBOX-FOREIGN-V1')->exists()||User::where('username','bb.kasir.overlap')->exists())throw new SafetyException('Fixture advanced sudah ada. Tidak ditimpa atau direset.');
            $db->transaction(fn()=>(new Fixtures)->advanced());return $this->status();
        });
    }
    public function status(): array
    {
        Guard::application($this->app);
        $db=$this->app->make('db')->connection('blackbox');
        if(!$db->getSchemaBuilder()->hasTable('businesses'))return ['environment'=>'blackbox','database'=>'laundryku_blackbox','prepared'=>false,'manual_results'=>'Belum diuji manual'];
        return ['environment'=>'blackbox','database'=>'laundryku_blackbox','prepared'=>Business::where('code','BBOX-MANUAL-V1')->exists(),
            'advanced'=>Business::where('code','BBOX-FOREIGN-V1')->exists(),'manual_results'=>'Belum diuji manual',
            'storage'=>'storage/blackbox','accounts'=>User::orderBy('id')->get(['username','role','branch_id'])->toArray(),
            'orders'=>Order::orderBy('id')->get(['id','order_number','branch_id','status','total'])->toArray(),
            'shifts'=>CashShift::orderBy('id')->get(['id','cashier_id','branch_id','status','opening_cash'])->map(fn($s)=>[...$s->toArray(),'linked_paid_cash'=>$s->paidCashPayments()->sum('amount')])->all(),
            'payments'=>Payment::count()];
    }
}
