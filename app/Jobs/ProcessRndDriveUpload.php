<?php
namespace App\Jobs;
use App\Models\RndArchiveFile;
use App\Models\RndUploadTask;
use App\Services\GoogleDriveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;
class ProcessRndDriveUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $timeout = 240;
    public int $tries = 1; // Hindari duplikasi Drive jika respons API terputus.
    public function __construct(public string $taskId) {}
    public function handle(GoogleDriveService $drive): void
    {
        $task = RndUploadTask::findOrFail($this->taskId);
        if ($task->status === 'done' || $task->status === 'failed') return;
        $task->update(['status' => 'processing']);
        try {
            $folder = \App\Models\RndArchiveFolder::findOrFail($task->folder_id);
            $path = Storage::disk('local')->path($task->local_path);
            if (!is_file($path)) throw new \RuntimeException('File sementara tidak ditemukan.');
            $result = $drive->uploadFile($path, $task->original_name, $folder->google_drive_folder_id, $task->mime_type);
            if (empty($result['id'])) throw new \RuntimeException('Google Drive tidak mengembalikan file ID.');
            // Simpan ID Drive sebelum insert DB; jika insert gagal, jangan ulang upload.
            $task->update(['google_drive_file_id' => $result['id']]);
            $file = RndArchiveFile::create([
                'folder_id' => $task->folder_id,
                'original_name' => $task->original_name,
                'google_drive_file_id' => $result['id'],
                'mime_type' => $result['mime_type'] ?? $task->mime_type,
                'file_size' => (int) ($result['size'] ?? $task->file_size),
                'uploaded_by' => $task->user_id,
            ]);
            $task->update(['status' => 'done', 'archive_file_id' => $file->id]);
            Cache::forget('rnd_drive_folder_items:' . $folder->google_drive_folder_id);
        } catch (Throwable $e) {
            Log::error('RND queue Drive gagal', ['task_id' => $task->id, 'error' => $e->getMessage()]);
            $task->update(['status' => 'failed', 'error_message' => mb_substr($e->getMessage(), 0, 2000)]);
        } finally {
            // Hapus file sementara setelah job berakhir, berhasil ataupun gagal.
            Storage::disk('local')->delete($task->local_path);
        }
    }
}
