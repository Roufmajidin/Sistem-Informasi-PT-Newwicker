<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class BomTimbangan extends Model
{
    protected $table = 'bom_timbangan';
    protected $guarded = [];
    public static function rules(): array
    {
        return ['timbangan_files' => 'sometimes|array|max:20', 'timbangan_files.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120', 'timbangan_remove' => 'sometimes|array|max:100', 'timbangan_remove.*' => 'uuid'];
    }
    public static function syncPhotos($bom, $request, array &$new, array &$old): void
    {
        $type = $bom instanceof BomProd ? 'production' : 'sample';
        $remove = $request->input('timbangan_remove', []);
        foreach ($bom->timbangan()->whereIn('client_key', $remove)->get() as $photo) {
            $old[] = $photo->image;
            $photo->delete();
        }
        $sort = (int) $bom->timbangan()->max('sort_order');
        foreach ($request->file('timbangan_files', []) as $key => $file) {
            if (!\Illuminate\Support\Str::isUuid($key)) throw new \InvalidArgumentException('ID foto tidak valid.');
            if (in_array($key, $remove, true) || $bom->timbangan()->where('client_key', $key)->exists()) continue;
            $path = $file->store('bom-timbangan/'.$type.'/'.$bom->id, 'public');
            if (!$path) throw new \RuntimeException('Gagal menyimpan foto timbangan.');
            $new[] = $path;
            self::create(['bom_type'=>$type, 'bom_id'=>$bom->id, 'client_key'=>$key, 'image'=>$path, 'sort_order'=>++$sort]);
        }
    }
    public static function cleanup(array $paths): void
    {
        foreach ($paths as $path) {
            try { Storage::disk('public')->delete($path); } catch (\Throwable $e) { report($e); }
        }
    }
}
