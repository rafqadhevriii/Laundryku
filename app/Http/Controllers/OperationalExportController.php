<?php
namespace App\Http\Controllers;
use App\Models\{Order,InventoryItem,CashShift,InventoryMovement};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class OperationalExportController extends Controller {
 public function excel(Request $request){
  $u=$request->user();
  $input=$request->validate(['start'=>['nullable','date'],'end'=>['nullable','date','after_or_equal:start'],'branch_id'=>['nullable',Rule::exists('branches','id')->where('business_id',$u->business_id)]]);
  $start=$input['start']??now()->startOfMonth()->toDateString();
  $end=$input['end']??now()->toDateString();
  $branch=app(\App\Services\ActiveBranchContext::class)->reportBranch($request,$input['branch_id']??null);
  $orders=Order::where('business_id',$u->business_id)->when($branch,fn($q)=>$q->where('branch_id',$branch))->whereDate('created_at','>=',$start)->whereDate('created_at','<=',$end);
  $stock=InventoryItem::where('business_id',$u->business_id)->when($branch,fn($q)=>$q->where('branch_id',$branch));
  $shifts=CashShift::where('business_id',$u->business_id)->when($branch,fn($q)=>$q->where('branch_id',$branch))->whereDate('opened_at','>=',$start)->whereDate('opened_at','<=',$end);
  $moves=InventoryMovement::whereHas('item',fn($q)=>$q->where('business_id',$u->business_id)->when($branch,fn($q)=>$q->where('branch_id',$branch)))->whereDate('created_at','>=',$start)->whereDate('created_at','<=',$end);
  $book=new Spreadsheet();
  $summary=$book->getActiveSheet();$summary->setTitle('Ringkasan');
  $branchName=$branch?$u->business->branches()->whereKey($branch)->value('name'):'Semua cabang';
  $summary->fromArray([['LAPORAN OPERASIONAL LAUNDRYKU'],['Usaha',$u->business->name],['Cabang',$branchName],['Mulai',$start],['Sampai',$end],[],['Status pesanan','Jumlah']],null,'A1');
  $counts=(clone $orders)->selectRaw('status,COUNT(*) as total')->groupBy('status')->pluck('total','status');
  $row=8;foreach(['received'=>'Diterima','processing'=>'Diproses','ready'=>'Siap diambil','picked_up'=>'Sudah diambil','cancelled'=>'Dibatalkan'] as $key=>$label)$summary->fromArray([$label,(int)($counts[$key]??0)],null,'A'.$row++);
  $summary->fromArray(['Shift dibuka',(clone $shifts)->count()],null,'A'.++$row);
  $summary->fromArray(['Shift menunggu review',(clone $shifts)->where('status','submitted')->count()],null,'A'.++$row);
  $summary->fromArray(['Selisih kas shift ditutup',(float)(clone $shifts)->whereIn('status',['submitted','reviewed'])->sum('difference')],null,'A'.++$row);
  $overdue=$book->createSheet();$overdue->setTitle('Pesanan Terlambat');
  $overdue->fromArray(['Nomor','Cabang','Pelanggan','Status','Estimasi selesai'],null,'A1');$row=2;
  foreach((clone $orders)->with('customer','branch')->whereIn('status',['received','processing'])->whereNotNull('estimated_at')->where('estimated_at','<',now())->orderBy('estimated_at')->cursor() as $o)$overdue->fromArray([$o->order_number,$o->branch?->name,$o->customer?->name,$o->status,$o->estimated_at?->format('Y-m-d H:i')],null,'A'.$row++);
  $low=$book->createSheet();$low->setTitle('Stok Menipis');$low->fromArray(['Bahan','Cabang','Stok','Minimum','Satuan'],null,'A1');$row=2;
  foreach((clone $stock)->with('branch')->whereColumn('quantity','<=','minimum_quantity')->orderBy('name')->cursor() as $item)$low->fromArray([$item->name,$item->branch?->name,(float)$item->quantity,(float)$item->minimum_quantity,$item->unit],null,'A'.$row++);
  $shiftSheet=$book->createSheet();$shiftSheet->setTitle('Shift Kasir');$shiftSheet->fromArray(['Dibuka','Cabang','Kasir','Status','Kas Awal','Kas Seharusnya','Kas Dihitung','Selisih'],null,'A1');$row=2;
  foreach((clone $shifts)->with('cashier','branch')->orderBy('opened_at')->cursor() as $s)$shiftSheet->fromArray([$s->opened_at?->format('Y-m-d H:i'),$s->branch?->name,$s->cashier?->name,$s->status,(float)$s->opening_cash,$s->expected_cash===null?null:(float)$s->expected_cash,$s->closing_cash===null?null:(float)$s->closing_cash,$s->difference===null?null:(float)$s->difference],null,'A'.$row++);
  $movement=$book->createSheet();$movement->setTitle('Aktivitas Stok');$movement->fromArray(['Jenis','Status','Jumlah transaksi'],null,'A1');$row=2;
  foreach((clone $moves)->selectRaw('type,status,COUNT(*) as total')->groupBy('type','status')->get() as $m)$movement->fromArray([$m->type,$m->status,(int)$m->total],null,'A'.$row++);
  foreach($book->getAllSheets() as $sheet){$sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);foreach(range('A',$sheet->getHighestColumn()) as $column)$sheet->getColumnDimension($column)->setWidth(25);$sheet->freezePane('A2');}
  $summary->getColumnDimension('A')->setWidth(39);
  $book->setActiveSheetIndex(0);
  return response()->streamDownload(function()use($book){(new Xlsx($book))->save('php://output');$book->disconnectWorksheets();},'laporan-operasional-'.$start.'-'.$end.'.xlsx',['Content-Type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
 }
}
