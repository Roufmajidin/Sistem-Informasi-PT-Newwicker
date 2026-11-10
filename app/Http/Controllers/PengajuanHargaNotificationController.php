<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengajuanHargaNotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('pengajuan_harga_notifications')->where('user_id', $request->user()->id);
        return response()->json([
            'unread' => (clone $query)->whereNull('read_at')->count(),
            'notifications' => $query->orderByDesc('id')->limit(30)->get(),
        ]);
    }
    public function open(Request $request, int $id)
    {
        $row = DB::table('pengajuan_harga_notifications')
            ->where('id', $id)->where('user_id', $request->user()->id)->first();
        abort_unless($row, 404);
        DB::table('pengajuan_harga_notifications')->where('id', $id)
            ->where('user_id', $request->user()->id)->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);
        // Destination determined from current user, never blindly trust a stored URL.
        $email = strtolower(trim((string) $request->user()->email));
        $management = collect(['info@','office@','factory@','manager@','director@','admin@'])
            ->contains(fn ($prefix) => str_starts_with($email, $prefix));
        return redirect($management ? '/pengajuan-harga/directur' : '/pengajuan-harga');
    }
}
