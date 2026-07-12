<?php
namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\CadModel;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\DetailPo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CadController extends Controller
{
    //
    public function all()
    {
      $latestIds = CadModel::select(DB::raw('MAX(version) as version'), 'article_code')
    ->groupBy('article_code')
    ->get();

    $cads = CadModel::with('user')
        ->get()
        ->groupBy('article_code')
        ->map(fn ($items) => $items->sortByDesc('version')->first())
        ->values();

        return view('pages.rnd.cad.index', compact('cads', 'latestIds'));
    }
    public function history($article)
    {
        $histories = CadModel::with('user')
            ->where('article_code', $article)
            ->orderByDesc('version')
            ->get()
            ->map(function ($item) {
                return [
                    'id'            => $item->id,
                    'version'       => $item->version,
                    'status'        => $item->status,
                    'file_path'     => $item->file_path,
                    'master_sample' => $item->master_sample,
                    'uploaded_by'   => $item->user->name ?? '-',
                    'created_at'    => $item->created_at?->format('d M Y H:i'),
                ];
            });

        return response()->json($histories);
    }
    public function index($id)
    {
        $find = DetailPo::where(function ($q) use ($id) {
            $q->where('detail->article_nr_', $id)
                ->orWhere('detail->article_nr_nw', $id)
                ->orWhere('detail->nw_code', $id);
        })
            ->latest()
            ->first();
        // dd($find);
        $cads = CadModel::with('user')
            ->where('article_code', $id)
            ->orderByDesc('version')
            ->get();

        // 🔥 AMBIL BOM + GROUP + ITEM
        $bom = Bom::with('groups.items')
            ->where('article_number', $id)
            ->latest()
            ->first();
// dd($id, $bom);
        return view('pages.rnd.index', compact('find', 'cads', 'id', 'bom'));
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
        // dd($request->all());
        CadModel::create([
            'article_code'  => $request->article_code,
            'file_path'     => $path,
            'version'       => $version,
            'uploaded_by'   => auth()->id(),
            'status'        => 'draft',
            'master_sample' => $request->master_sample,

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
            ->orderBy('id', 'asc')
            ->get();
    }
}
