<?php
namespace App\Http\Controllers;
use App\Models\ProgressReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
class ProgressReportController extends Controller {
    private function viewerOnly(): bool {
        return str_starts_with(strtolower(trim((string) auth()->user()->email)), 'info@');
    }
    private function access(ProgressReport $report, bool $write = false): void {
        if ($this->viewerOnly()) abort_unless(!$write && $report->status === 'PUBLISHED', 403);
        abort_unless((int)$report->created_by === (int)auth()->id() || (!$write && $report->status === 'PUBLISHED'), 403);
        if ($write) abort_unless($report->status === 'DRAFT', 409, 'Laporan sudah dipublikasikan.');
    }
    public function index(Request $request) {
        $viewerOnly = $this->viewerOnly();
        $tab = !$viewerOnly && $request->query('tab') === 'draft' ? 'draft' : 'published';
        $draftCount = $viewerOnly ? 0 : ProgressReport::where('created_by', auth()->id())->where('status','DRAFT')->count();
        $publishedCount = ProgressReport::where('status','PUBLISHED')->count();
        $reports = ProgressReport::with('creator')->withCount('details')
            ->where('status', $tab === 'draft' ? 'DRAFT' : 'PUBLISHED')
            ->when($tab === 'draft', fn($q) => $q->where('created_by', auth()->id()))
            ->when($request->filled('q'), function($q) use ($request) {
                $term = '%'.substr((string)$request->query('q'),0,100).'%';
                $q->where(fn($q) => $q->whereHas('creator',fn($u)=>$u->where('name','like',$term))->orWhereHas('details',fn($d)=>$d->where('subject','like',$term)));
            })->latest('report_date')->latest('id')->paginate(15)->withQueryString();
        return view('pages.progress_report.index',compact('reports','viewerOnly','tab','draftCount','publishedCount'));
    }
    public function create() { abort_if($this->viewerOnly(),403); return view('pages.progress_report.form',['report'=>new ProgressReport(),'users'=>User::orderBy('name')->get(['id','name'])]); }
    public function edit(ProgressReport $progress_report) {
        $this->access($progress_report,true);
        return view('pages.progress_report.form',['report'=>$progress_report->load('details'),'users'=>User::orderBy('name')->get(['id','name'])]);
    }
    private function validated(Request $request): array {
        return $request->validate([
            'report_date'=>'required|date','period_start'=>'required|date','period_end'=>'required|date|after_or_equal:period_start',
            'details'=>'nullable|array|max:100','details.*.id'=>'nullable|integer|distinct',
            'details.*.subject'=>'required|string|max:255','details.*.status'=>'required|in:OPEN,IN_PROGRESS,BLOCKED,DONE',
            'details.*.is_urgent'=>'nullable|boolean','details.*.problem_order_impact'=>'required|string|max:3000',
            'details.*.action'=>'required|string|max:3000','details.*.pic_id'=>'required|exists:users,id',
            'details.*.deadline'=>'required|date','details.*.result_decision'=>'nullable|string|max:3000',
        ]);
    }
    private function sync(ProgressReport $report,array $rows): void {
        $keep=[];
        foreach($rows as $row) {
            $id=$row['id']??null; unset($row['id']);
            $row['is_urgent']=$row['is_urgent']??false;
            $row['result_decision']=$row['result_decision']??null;
            if ($id) {
                $detail=$report->details()->whereKey($id)->firstOrFail();
                $detail->update($row+['updated_by'=>auth()->id()]);
            } else { $detail=$report->details()->create($row+['created_by'=>auth()->id()]); }
            $keep[]=$detail->id;
        }
        $report->details()->whereNotIn('id',$keep)->delete();
    }
    public function store(Request $request) {
        abort_if($this->viewerOnly(),403);
        $data=$this->validated($request);
        $report=DB::transaction(function() use($data){
            $rows=$data['details']??[]; unset($data['details']);
            $report=ProgressReport::create($data+['created_by'=>auth()->id(),'status'=>'DRAFT']);
            $this->sync($report,$rows); return $report;
        });
        return redirect()->route('progress-report.edit',$report)->with('success','Draft tersimpan.');
    }
    public function update(Request $request,ProgressReport $progress_report) {
        $data=$this->validated($request);
        DB::transaction(function() use($data,$progress_report){
            $report=ProgressReport::lockForUpdate()->findOrFail($progress_report->id); $this->access($report,true);
            $rows=$data['details']??[]; unset($data['details']);
            $report->update($data+['updated_by'=>auth()->id()]); $this->sync($report,$rows);
        });
        return back()->with('success','Draft tersimpan.');
    }
    public function show(ProgressReport $progress_report) {
        $this->access($progress_report);
        return view('pages.progress_report.show',['report'=>$progress_report->load('creator','details.pic')]);
    }
    public function publish(ProgressReport $progress_report) {
        DB::transaction(function() use($progress_report){
            $report=ProgressReport::lockForUpdate()->findOrFail($progress_report->id); $this->access($report,true);
            $rows=$report->details()->get();
            if($rows->isEmpty() || $rows->contains(fn($r)=>$r->status==='DONE' && blank($r->result_decision))) throw ValidationException::withMessages(['details'=>'Minimal satu baris; status DONE wajib memiliki hasil.']);
            $report->update(['status'=>'PUBLISHED','published_by'=>auth()->id(),'published_at'=>now(),'updated_by'=>auth()->id()]);
        });
        return redirect()->route('progress-report.show',$progress_report)->with('success','Laporan dipublikasikan.');
    }
    public function destroy(ProgressReport $progress_report) {
        DB::transaction(function() use($progress_report){
            $report=ProgressReport::lockForUpdate()->findOrFail($progress_report->id); $this->access($report,true); $report->details()->delete(); $report->delete();
        });
        return redirect()->route('progress-report.index',['tab'=>'draft'])->with('success','Draft dihapus.');
    }
    public function pdf(ProgressReport $progress_report) {
        $this->access($progress_report); abort_unless($progress_report->status==='PUBLISHED',403);
        return Pdf::loadView('pages.progress_report.pdf',['report'=>$progress_report->load('creator','details.pic')])->setPaper('a4','landscape')->download('progress-report-'.$progress_report->id.'.pdf');
    }
}
