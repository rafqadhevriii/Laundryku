<?php
namespace App\Support\Blackbox;

use App\Models\{AuditLog,Branch,Business,CashShift,Customer,InventoryItem,Order,Payment,Service,User};

/** Synthetic fixtures, called only after the isolated DB/grant guards and within a transaction. */
class Fixtures
{
    private function user(Business $business,string $username,string $role,?Branch $branch=null): User
    {
        return User::create(['business_id'=>$business->id,'branch_id'=>$branch?->id,'name'=>'Uji '.$username,
            'username'=>$username,'email'=>$username.'@example.test','password'=>getenv('BLACKBOX_ACCOUNT_PASSWORD'),'role'=>$role,'is_active'=>true]);
    }
    private function branch(Business $business,string $name,string $code): Branch
    {
        $branch=Branch::create(['business_id'=>$business->id,'name'=>$name,'code'=>$code]);$branch->settings()->create([]);return $branch;
    }
    private function order(User $actor,Branch $branch,Customer $customer,Service $service,string $number,float $quantity=1,string $status='ready',bool $late=false): Order
    {
        $total=round($quantity*(float)$service->price,2);
        $order=Order::create(['business_id'=>$branch->business_id,'branch_id'=>$branch->id,'customer_id'=>$customer->id,'created_by'=>$actor->id,
            'order_number'=>$number,'status'=>$status,'subtotal'=>$total,'total'=>$total,
            'estimated_at'=>$late?now()->subDay():now()->addHours($service->estimated_hours),'notes'=>'DATA SINTETIS BLACK BOX; bukan transaksi nyata.']);
        $order->items()->create(['service_id'=>$service->id,'service_name'=>$service->name,'unit'=>$service->unit,'quantity'=>$quantity,'unit_price'=>$service->price,'subtotal'=>$total]);return $order;
    }
    private function payment(Order $order,User $cashier,string $method='cash',string $status='paid',?CashShift $shift=null): Payment
    {
        return Payment::create(['order_id'=>$order->id,'recorded_by'=>$cashier->id,'method'=>$method,'status'=>$status,'amount'=>$order->total,
            'cash_shift_id'=>$shift?->id,'paid_at'=>$status==='paid'?now():null,'notes'=>$status==='rejected'?'Bukti sintetis belum sesuai':'Fixture sintetis manual']);
    }
    public function initial(): void
    {
        Guard::application(app());
        $business=Business::create(['name'=>'Laundryku Black Box · Sintetis','code'=>'BBOX-MANUAL-V1']);$business->settings()->create([]);
        $a=$this->branch($business,'Cabang Uji A','A');$b=$this->branch($business,'Cabang Uji B','B');
        $owner=$this->user($business,'bb.pemilik','owner');$ca=$this->user($business,'bb.kasir.a','cashier',$a);$cb=$this->user($business,'bb.kasir.b','cashier',$b);
        $customers=[];foreach([$a,$b] as $branch)foreach([1,2] as $i)$customers[$branch->code][$i]=Customer::create(['business_id'=>$business->id,'branch_id'=>$branch->id,'name'=>'Pelanggan Uji '.$branch->code.'-'.$i,'notes'=>'Identitas sintetis; tanpa nomor kontak nyata.']);
        $kg=Service::create(['business_id'=>$business->id,'name'=>'Cuci Kering Uji','unit'=>'kg','price'=>8000,'minimum_weight'=>2,'estimated_hours'=>24]);
        $item=Service::create(['business_id'=>$business->id,'name'=>'Setrika Item Uji','unit'=>'item','price'=>15000,'estimated_hours'=>48]);
        $paket=Service::create(['business_id'=>$business->id,'name'=>'Paket Tunai 25 Uji','unit'=>'item','price'=>25000,'estimated_hours'=>24]);
        foreach([$a,$b] as $branch)InventoryItem::create(['business_id'=>$business->id,'branch_id'=>$branch->id,'name'=>'Deterjen Uji '.$branch->code,'category'=>'Deterjen','unit'=>'liter','quantity'=>$branch->id===$a->id?2:20,'minimum_quantity'=>5]);
        $sa=CashShift::create(['business_id'=>$business->id,'branch_id'=>$a->id,'cashier_id'=>$ca->id,'opening_cash'=>100000,'opened_at'=>now()->subHours(2),'status'=>'open']);
        CashShift::create(['business_id'=>$business->id,'branch_id'=>$b->id,'cashier_id'=>$cb->id,'opening_cash'=>100000,'opened_at'=>now()->subHour(),'status'=>'open']);
        $this->order($ca,$a,$customers['A'][1],$kg,'BB-A-MIN',0.5,'received');
        $this->payment($this->order($ca,$a,$customers['A'][1],$paket,'BB-A-CASH25'),$ca,'cash','paid',$sa);
        $this->payment($this->order($ca,$a,$customers['A'][2],$item,'BB-A-CASH15'),$ca,'cash','paid',$sa);
        $this->order($ca,$a,$customers['A'][2],$kg,'BB-A-LATE',2.5,'processing',true);
        $this->order($cb,$b,$customers['B'][1],$kg,'BB-B-UNPAID',1,'received');
        $this->payment($this->order($cb,$b,$customers['B'][2],$item,'BB-B-DIGITAL',2),$cb,'transfer');
        AuditLog::create(['business_id'=>$business->id,'user_id'=>$owner->id,'action'=>'blackbox.fixtures_prepared','subject_type'=>'business','subject_id'=>$business->id,'details'=>['synthetic'=>true,'manual_results'=>'Belum diuji manual']]);
    }
    public function advanced(): void
    {
        Guard::application(app());
        $business=Business::where('code','BBOX-MANUAL-V1')->firstOrFail();$owner=User::where('username','bb.pemilik')->firstOrFail();
        $a=$business->branches()->where('code','A')->firstOrFail();$b=$business->branches()->where('code','B')->firstOrFail();
        $ca=User::where('username','bb.kasir.a')->firstOrFail();$cb=User::where('username','bb.kasir.b')->firstOrFail();
        $kg=Service::where('business_id',$business->id)->where('unit','kg')->firstOrFail();$item=Service::where('business_id',$business->id)->where('unit','item')->where('price',15000)->firstOrFail();
        $customer=Customer::where('branch_id',$a->id)->firstOrFail();$customerB=Customer::where('branch_id',$b->id)->firstOrFail();
        if(!CashShift::where('cashier_id',$ca->id)->where('status','open')->exists())CashShift::create(['business_id'=>$business->id,'branch_id'=>$a->id,'cashier_id'=>$ca->id,'opening_cash'=>100000,'opened_at'=>now()->subHour(),'status'=>'open']);
        $overlap=$this->user($business,'bb.kasir.overlap','cashier',$a);
        CashShift::create(['business_id'=>$business->id,'branch_id'=>$a->id,'cashier_id'=>$overlap->id,'opening_cash'=>50000,'opened_at'=>now()->subHour(),'status'=>'open']);
        CashShift::create(['business_id'=>$business->id,'branch_id'=>$a->id,'cashier_id'=>$ca->id,'opening_cash'=>100000,'expected_cash'=>100000,'closing_cash'=>100000,'difference'=>0,'status'=>'reviewed','opened_at'=>now()->subDays(2),'closed_at'=>now()->subDay(),'reviewed_by'=>$owner->id]);
        foreach(['BB-H-ASSIGN','BB-H-UNALLOC','BB-H-BLOCK','BB-H-OVERLAP','BB-H-LATER'] as $number)$this->payment($this->order($ca,$a,$customer,$item,$number),$ca);
        $this->payment($this->order($cb,$b,$customerB,$item,'BB-H-B'),$cb);
        foreach(['BB-RETRY-TRANSFER'=>'transfer','BB-RETRY-QRIS'=>'qris','BB-RETRY-LOOP'=>'transfer'] as $number=>$method)$this->payment($this->order($ca,$a,$customer,$item,$number),$ca,$method,'rejected');
        $this->payment($this->order($ca,$a,$customer,$item,'BB-DIGITAL-PENDING'),$ca,'transfer','pending');
        $foreign=Business::create(['name'=>'Usaha Asing Uji · Sintetis','code'=>'BBOX-FOREIGN-V1']);$foreign->settings()->create([]);$f=$this->branch($foreign,'Cabang Asing Uji','F');
        $this->user($foreign,'bb.pemilik.asing','owner');$fc=$this->user($foreign,'bb.kasir.asing','cashier',$f);
        $cu=Customer::create(['business_id'=>$foreign->id,'branch_id'=>$f->id,'name'=>'Pelanggan Asing Uji']);
        $sv=Service::create(['business_id'=>$foreign->id,'name'=>'Layanan Asing Uji','unit'=>'item','price'=>15000,'estimated_hours'=>24]);
        CashShift::create(['business_id'=>$foreign->id,'branch_id'=>$f->id,'cashier_id'=>$fc->id,'opening_cash'=>100000,'opened_at'=>now()->subHour(),'status'=>'open']);
        $this->payment($this->order($fc,$f,$cu,$sv,'BB-FOREIGN-RETRY'),$fc,'qris','rejected');
        AuditLog::create(['business_id'=>$business->id,'user_id'=>$owner->id,'action'=>'blackbox.advanced_prepared','subject_type'=>'business','subject_id'=>$business->id,'details'=>['synthetic'=>true,'manual_results'=>'Belum diuji manual']]);
    }
}
