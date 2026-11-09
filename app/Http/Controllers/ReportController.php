<?php
namespace App\Http\Controllers;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\{Alignment,Fill,Font};
class ReportController extends Controller {
 private function data(Request $request):array {
  $u=$request->user();
  $input=$request->validate([
   'start'=>['nullable','date'],'end'=>['nullable','date','after_or_equal:start'],
   'branch_id'=>['nullable',Rule::exists('branches','id')->where('business_id',$u->business_id)],
  ]);
  $start=$input['start']??now()->startOfMonth()->toDateString();
  $end=$input['end']??now()->toDateString();
  $branch=$input['branch_id']??null;
  $orders=Order::where('business_id',$u->business_id)->when($branch,fn($q)=>$q->where('branch_id',$branch))
   ->whereDate('created_at','>=',$start)->whereDate('created_at','<=',$end);
  $payments=Payment::where('status','paid')->whereDate('paid_at','>=',$start)->whereDate('paid_at','<=',$end)
   ->whereHas('order',fn($q)=>$q->where('business_id',$u->business_id)->when($branch,fn($q)=>$q->where('branch_id',$branch)));
  $expenses=Expense::where('business_id',$u->business_id)->when($branch,fn($q)=>$q->where('branch_id',$branch))
   ->whereDate('expense_date','>=',$start)->whereDate('expense_date','<=',$end);
  return compact('start','end','branch','orders','payments','expenses');
 }
 public function index(Request $request){
  $data=$this->data($request);
  $totalRevenue=(clone $data['payments'])->sum('amount');
  $totalExpenses=(clone $data['expenses'])->sum('amount');
  $totalOrders=(clone $data['orders'])->count();
  $payments=(clone $data['payments'])->with('order.customer','order.branch')->latest('paid_at')->paginate(15)->withQueryString();
  $branches=$request->user()->business->branches()->orderBy('name')->get();
  return view('reports.index',[...$data,'totalRevenue'=>$totalRevenue,'totalExpenses'=>$totalExpenses,'totalOrders'=>$totalOrders,'payments'=>$payments,'branches'=>$branches]);
 }
 public function pdf(Request $request){
  $data=$this->data($request);
  $payments=(clone $data['payments'])->with('order.customer','order.branch')->orderBy('paid_at')->get();
  $totalRevenue=(clone $data['payments'])->sum('amount');
  $totalExpenses=(clone $data['expenses'])->sum('amount');
  $totalOrders=(clone $data['orders'])->count();
  $business=$request->user()->business;
  $branchName=$data['branch']?$business->branches()->whereKey($data['branch'])->value('name'):'Semua cabang';
  return Pdf::loadView('reports.pdf',[...$data,'payments'=>$payments,'totalRevenue'=>$totalRevenue,'totalExpenses'=>$totalExpenses,'totalOrders'=>$totalOrders,'business'=>$business,'branchName'=>$branchName])->setPaper('a4')->download('laporan-pendapatan-'.$data['start'].'-'.$data['end'].'.pdf');
 }
 public function excel(Request $request){
  $data=$this->data($request);
  $business=$request->user()->business;
  $branchName=$data['branch']?$business->branches()->whereKey($data['branch'])->value('name'):'Semua cabang';
  $revenue=(float)(clone $data['payments'])->sum('amount');
  $expenses=(float)(clone $data['expenses'])->sum('amount');
  $orders=(clone $data['orders'])->count();
  $book=new Spreadsheet();
  $summary=$book->getActiveSheet();$summary->setTitle('Ringkasan');
  $summary->fromArray([
   ['LAPORAN PENDAPATAN LAUNDRYKU'],
   ['Usaha',$business->name],['Cabang',$branchName],['Mulai',$data['start']],['Sampai',$data['end']],
   [],['Keterangan','Nilai (Rp)'],['Pembayaran diterima',$revenue],['Pengeluaran',$expenses],['Selisih penerimaan dan pengeluaran',$revenue-$expenses],['Jumlah pesanan dibuat',$orders],
   [],['Catatan','Selisih bukan laba bersih akuntansi.']
  ],null,'A1');
  $summary->mergeCells('A1:B1');$summary->getStyle('A1:B1')->getFont()->setBold(true)->setSize(16);
  $summary->getStyle('A7:B7')->getFont()->setBold(true);
  $summary->getStyle('B8:B10')->getNumberFormat()->setFormatCode('#,##0.00');
  $summary->getColumnDimension('A')->setWidth(40);$summary->getColumnDimension('B')->setWidth(43);
  $detail=$book->createSheet();$detail->setTitle('Pembayaran');
  $detail->fromArray(['Tanggal Bayar','Nomor Pesanan','Pelanggan','Cabang','Metode','Jumlah (Rp)'],null,'A1');
  $row=2;
  foreach((clone $data['payments'])->with('order.customer','order.branch')->orderBy('paid_at')->cursor() as $p){
   $detail->fromArray([$p->paid_at?->format('Y-m-d H:i'),$p->order?->order_number,$p->order?->customer?->name,$p->order?->branch?->name,strtoupper($p->method),(float)$p->amount],null,'A'.$row++);
  }
  $detail->getStyle('A1:F1')->getFont()->setBold(true);
  $detail->getStyle('F2:F'.max($row,2))->getNumberFormat()->setFormatCode('#,##0.00');
  foreach(['A'=>20,'B'=>30,'C'=>28,'D'=>25,'E'=>16,'F'=>20] as $col=>$width)$detail->getColumnDimension($col)->setWidth($width);
  $detail->freezePane('A2');$detail->setAutoFilter('A1:F'.max($row-1,1));
  $outgoing=$book->createSheet();$outgoing->setTitle('Pengeluaran');
  $outgoing->fromArray(['Tanggal','Cabang','Kategori','Keterangan','Jumlah (Rp)'],null,'A1');
  $expenseRow=2;
  foreach((clone $data['expenses'])->with('branch')->orderBy('expense_date')->cursor() as $expense){
   $outgoing->fromArray([$expense->expense_date?->format('Y-m-d'),$expense->branch?->name,$expense->category,$expense->description,(float)$expense->amount],null,'A'.$expenseRow++);
  }
  $outgoing->getStyle('A1:E1')->getFont()->setBold(true);
  $outgoing->getStyle('E2:E'.max($expenseRow,2))->getNumberFormat()->setFormatCode('#,##0.00');
  foreach(['A'=>18,'B'=>26,'C'=>32,'D'=>42,'E'=>20] as $col=>$width)$outgoing->getColumnDimension($col)->setWidth($width);
  $outgoing->freezePane('A2');$outgoing->setAutoFilter('A1:E'.max($expenseRow-1,1));
  $book->setActiveSheetIndex(0);
  $filename='laporan-pendapatan-'.$data['start'].'-'.$data['end'].'.xlsx';
  return response()->streamDownload(function()use($book){(new Xlsx($book))->save('php://output');$book->disconnectWorksheets();},$filename,['Content-Type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
 }
 public function export(Request $request){
  $data=$this->data($request);
  $rows=(clone $data['payments'])->with('order.customer','order.branch')->orderBy('paid_at')->get();
  $filename='laporan-pendapatan-'.$data['start'].'-'.$data['end'].'.csv';
  return response()->streamDownload(function()use($rows){
   $out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");
   fputcsv($out,['Tanggal Bayar','Nomor Pesanan','Pelanggan','Cabang','Metode','Jumlah (Rp)'],';');
   foreach($rows as $p)fputcsv($out,[$p->paid_at?->format('Y-m-d H:i'),$p->order?->order_number,$p->order?->customer?->name,$p->order?->branch?->name,$p->method,$p->amount],';');
   fclose($out);
  },$filename,['Content-Type'=>'text/csv; charset=UTF-8']);
 }
}
