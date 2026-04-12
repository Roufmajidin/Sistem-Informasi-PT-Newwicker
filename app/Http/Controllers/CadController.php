<?php
namespace App\Http\Controllers;

use App\Models\CadModel;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\DetailPo;
use Illuminate\Http\Request;

class CadController extends Controller
{
    //
    public function index($id)
    {
        $find = DetailPo::where(function ($q) use ($id) {
            $q->where('detail->article_nr_', $id)
                ->orWhere('detail->article_nr_nw', $id)
                ->orWhere('detail->nw_code', $id);
        })
            ->latest() // 🔥 ambil berdasarkan created_at DESC
            ->first();

        $cads = CadModel::with('user')
            ->where('article_code', $id)
            ->orderByDesc('version')
            ->get();
        // dd($find);
        return view('pages.rnd.index', compact('find', 'cads', 'id'));
    }
    public function upload(Request $request)
    {
        $request->validate([
            'file'         => 'required|file|max:10240',
            'article_code' => 'required',
        ]);

        $file = $request->file('file');
        $path = $file->store('cad_files', 'public');

        // ambil version terakhir
        $latest = CadModel::where('article_code', $request->article_code)
            ->max('version');

        $version = $latest ? $latest + 1 : 1;

        CadModel::create([
            'article_code' => $request->article_code,
            'file_path'    => $path,
            'version'      => $version,
            'uploaded_by'  => auth()->id(),
            'status'       => 'draft',
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
    public function send(Request $request)
    {
        $msg = ChatMessage::create([
            'chat_room_id' => $request->room_id,
            'user_id'      => auth()->id(),
            'message'      => $request->message,
        ]);

        return response()->json($msg);
    }
    public function getRoom($itemId)
    {
        $room = ChatRoom::firstOrCreate([
            'po_item_id' => $itemId,
        ]);

        return response()->json([
            'room_id' => $room->id,
        ]);
    }
    public function messages($roomId)
{
    return ChatMessage::with('user')
        ->where('chat_room_id', $roomId)
        ->orderBy('id','asc')
        ->get();
}
}
