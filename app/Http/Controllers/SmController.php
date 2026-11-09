<?php
namespace App\Http\Controllers;
use App\Events\SmChanged;
use App\Models\MonitoringIssue;
use App\Models\MonitoringIssueMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class SmController extends Controller {
 private function isDirector():bool {return str_starts_with(strtolower(trim((string)(auth()->user()?->email ?? ''))), 'factory@');}
 private function canSee(MonitoringIssue $issue):bool {
  return $issue->status!=='draft' || (int)$issue->user_id===(int)auth()->id();
 }
 private function canEdit(MonitoringIssue $issue):bool {return $issue->user_id===auth()->id() && $issue->status==='draft';}
 public function index(Request $request){
  $user=auth()->user();
  $query=MonitoringIssue::query()->with('reporter')->withCount('messages');
  if(!$this->isDirector()){$query->where(fn($q)=>$q->where('status','!=','draft')->orWhere('user_id',$user->id));}
  else {$query->where('status','!=','draft');}
  if($request->filled('q')){$term=trim((string)$request->q);$query->where(fn($q)=>$q->where('problem','like',"%{$term}%")->orWhere('potential_impact','like',"%{$term}%"));}
  if($request->filled('status')){$query->where('status',$request->status);}
  $issues=$query->latest('id')->paginate(15)->withQueryString();
  return view('pages.sm.index',compact('issues'));
 }
 public function store(Request $request){
  $data=$request->validate(['problem'=>'required|string|max:10000','potential_impact'=>'nullable|string|max:10000']);
  $issue=MonitoringIssue::create([...$data,'user_id'=>auth()->id(),'status'=>'draft']);
  broadcast(new SmChanged($issue->id,'created'))->toOthers();
  return redirect()->route('sm.index')->with('success','Draft issue berhasil dibuat.');
 }
 public function update(Request $request,MonitoringIssue $issue){
  abort_unless($this->canEdit($issue),403,'Hanya writer dapat mengedit draft miliknya.');
  $issue->update($request->validate(['problem'=>'required|string|max:10000','potential_impact'=>'nullable|string|max:10000']));
  broadcast(new SmChanged($issue->id,'updated'))->toOthers();
  return back()->with('success','Draft berhasil diperbarui.');
 }
 public function release(MonitoringIssue $issue){
  abort_unless($this->canEdit($issue),403);
  $issue->update(['status'=>'open']);
  broadcast(new SmChanged($issue->id,'released'))->toOthers();
  return back()->with('success','Issue berhasil di-release.');
 }
 public function directorUpdate(Request $request,MonitoringIssue $issue){
  abort_unless($this->isDirector(),403,'Akses Director diperlukan.');
  abort_if($issue->status==='draft',422,'Draft belum di-release.');
  $data=$request->validate(['follow_director'=>'nullable|string|max:10000','status'=>['required',Rule::in(['open','following','done'])]]);
  $issue->update([...$data,'tanggal_done'=>$data['status']==='done'?($issue->tanggal_done??now()):null,'done_by'=>$data['status']==='done'?($issue->done_by??auth()->id()):null]);
  broadcast(new SmChanged($issue->id,'status'))->toOthers();
  return back()->with('success','Follow-up dan status diperbarui.');
 }
 public function destroy(MonitoringIssue $issue){
  abort_unless($this->canEdit($issue),403,'Hanya writer dapat menghapus draft miliknya.');
  $id=$issue->id;$issue->delete();
  broadcast(new SmChanged($id,'deleted'))->toOthers();
  return back()->with('success','Draft dihapus.');
 }
 public function messages(MonitoringIssue $issue){
  abort_unless($this->canSee($issue),403);
  return response()->json(['messages'=>$issue->messages()->with('sender:id,name')->oldest('id')->get()->map(fn($m)=>[
   'id'=>$m->id,'parent_id'=>$m->parent_id,'user'=>$m->sender?->name??'User','mine'=>$m->user_id===auth()->id(),'message'=>$m->message,
   'attachment_name'=>$m->attachment_name,
   'attachment_url'=>$m->attachment_path ? route('sm.attachments.download',[$issue,$m]) : null,
   'attachment_is_image'=>$m->attachment_path ? in_array(strtolower(pathinfo($m->attachment_name ?? '',PATHINFO_EXTENSION)),['png','jpg','jpeg','gif','webp','bmp']) : false,
   'time'=>$m->created_at?->format('d/m/Y H:i'),
  ])]);
 }
 public function sendMessage(Request $request,MonitoringIssue $issue){
  abort_unless($this->canSee($issue) && $issue->status!=='draft',403);
  $data=$request->validate([
   'message'=>'nullable|string|max:10000',
   'parent_id'=>'nullable|integer',
   'attachment'=>'nullable|file|max:20480|mimes:jpg,jpeg,png,gif,webp,bmp,pdf,doc,docx,xls,xlsx,csv,txt,zip,rar,7z,ppt,pptx',
  ]);
  if(!filled($data['message'] ?? null) && !$request->hasFile('attachment')){
   return response()->json(['message'=>'Isi pesan atau pilih attachment.'],422);
  }
  if(!empty($data['parent_id'])){
   abort_unless(MonitoringIssueMessage::where('monitoring_issue_id',$issue->id)->whereKey($data['parent_id'])->exists(),422,'Pesan balasan tidak valid.');
  }
  $attachmentPath=null;
  $attachmentName=null;
  if($request->hasFile('attachment')){
   $file=$request->file('attachment');
   $attachmentName=Str::limit(basename($file->getClientOriginalName()),200,'');
   $attachmentPath=$file->store('sm-attachments/'.$issue->id,'local');
  }
  try{
   $message=$issue->messages()->create([
    'user_id'=>auth()->id(),
    'message'=>$data['message'] ?? '',
    'parent_id'=>$data['parent_id']??null,
    'attachment_path'=>$attachmentPath,
    'attachment_name'=>$attachmentName,
   ]);
  }catch(\Throwable $e){
   if($attachmentPath) Storage::disk('local')->delete($attachmentPath);
   throw $e;
  }
  broadcast(new SmChanged($issue->id,'message',['message_id'=>$message->id]))->toOthers();
  return response()->json(['ok'=>true,'id'=>$message->id],201);
 }
 public function downloadAttachment(MonitoringIssue $issue,MonitoringIssueMessage $message){
  abort_unless($this->canSee($issue) && $issue->status!=='draft',403);
  abort_unless((int)$message->monitoring_issue_id===(int)$issue->id,404);
  abort_unless($message->attachment_path && Storage::disk('local')->exists($message->attachment_path),404);
  $name=$message->attachment_name ?: 'attachment';
  $ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));
  $mime=Storage::disk('local')->mimeType($message->attachment_path) ?: 'application/octet-stream';
  // Render only validated image formats inline; force download for other files.
  if(in_array($ext,['png','jpg','jpeg','gif','webp','bmp'],true) && str_starts_with($mime,'image/')){
   return response()->file(Storage::disk('local')->path($message->attachment_path),[
    'Content-Type'=>$mime,
    'Content-Disposition'=>'inline; filename="'.str_replace('"','',basename($name)).'"',
    'X-Content-Type-Options'=>'nosniff',
   ]);
  }
  return Storage::disk('local')->download($message->attachment_path,$name,['X-Content-Type-Options'=>'nosniff']);
 }

}
