<?php

namespace App\Http\Controllers;

use App\Events\SmChanged;
use App\Models\MonitoringIssue;
use App\Models\MonitoringIssueMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
class SmController extends Controller
{
    private function isDirector(): bool
    {
        return str_starts_with(strtolower(trim((string) (auth()->user()?->email ?? ''))), 'factory@');
    }

    private function canSee(MonitoringIssue $issue): bool
    {
        return $issue->status !== 'draft' || (int) $issue->user_id === (int) auth()->id();
    }

    private function canEdit(MonitoringIssue $issue): bool
    {
        return (int) $issue->user_id === (int) auth()->id() && $issue->status === 'draft';
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = MonitoringIssue::query()
            ->with('reporter')
            ->withCount('messages')
            ->withCount([
                'messages as unread_messages_count' => function ($q) use ($user) {
                    $q->where('user_id', '!=', $user->id)
                        ->whereDoesntHave('reads', function ($readQuery) use ($user) {
                            $readQuery->where('user_id', $user->id);
                        });
                },
            ]);

        if (!$this->isDirector()) {
            $query->where(function ($q) use ($user) {
                $q->where('status', '!=', 'draft')
                    ->orWhere('user_id', $user->id);
            });
        } else {
            $query->where('status', '!=', 'draft');
        }

        if ($request->filled('q')) {
            $term = trim((string) $request->q);

            $query->where(function ($q) use ($term) {
                $q->where('problem', 'like', "%{$term}%")
                    ->orWhere('potential_impact', 'like', "%{$term}%")
                    ->orWhere('comments', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $issues = $query->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('pages.sm.index', compact('issues'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'problem' => 'required|string|max:10000',
            'potential_impact' => 'nullable|string|max:10000',
        ]);
        $issue = MonitoringIssue::create([...$data, 'user_id' => auth()->id(), 'status' => 'draft']);
        broadcast(new SmChanged($issue->id, 'created'))->toOthers();
        return redirect()->route('sm.index')->with('success', 'Draft issue berhasil dibuat.');
    }

    public function update(Request $request, MonitoringIssue $issue)
    {
        abort_unless($this->canEdit($issue), 403, 'Hanya writer dapat mengedit draft miliknya.');
        $issue->update($request->validate([
            'problem' => 'required|string|max:10000',
            'potential_impact' => 'nullable|string|max:10000',
        ]));
        broadcast(new SmChanged($issue->id, 'updated'))->toOthers();
        return back()->with('success', 'Draft berhasil diperbarui.');
    }

    public function release(MonitoringIssue $issue)
    {
        abort_unless($this->canEdit($issue), 403);
        $issue->update(['status' => 'open']);
        broadcast(new SmChanged($issue->id, 'released'))->toOthers();
        return back()->with('success', 'Issue berhasil di-release.');
    }

    /**
     * Comments dapat ditulis dan diedit oleh user biasa pada issue yang sudah release.
     * Hanya kolom comments yang dapat diubah oleh endpoint ini.
     */
    public function updateComments(Request $request, MonitoringIssue $issue)
    {
        abort_unless(
            $this->canSee($issue) && $issue->status !== 'draft',
            403,
            'Comments hanya dapat diubah pada issue yang sudah di-release.'
        );

        $data = $request->validate([
            'comments' => ['nullable', 'string', 'max:10000'],
        ]);

        $issue->update(['comments' => $data['comments'] ?? null]);
        broadcast(new SmChanged($issue->id, 'comments'))->toOthers();

        return back()->with('success', 'Comments berhasil disimpan.');
    }

    public function directorUpdate(Request $request, MonitoringIssue $issue)
    {
        abort_unless($this->isDirector(), 403, 'Akses Director diperlukan.');
        abort_if($issue->status === 'draft', 422, 'Draft belum di-release.');
        $data = $request->validate([
            'follow_director' => 'nullable|string|max:10000',
        ]);
        $issue->update(['follow_director' => $data['follow_director'] ?? null]);
        broadcast(new SmChanged($issue->id, 'updated'))->toOthers();
        return back()->with('success', 'Follow-up Director berhasil diperbarui.');
    }

    public function closeIssue(MonitoringIssue $issue)
    {
        abort_unless(
            (int) $issue->user_id === (int) auth()->id(),
            403,
            'Hanya writer pembuat issue yang dapat menutup issue.'
        );
        abort_unless(
            in_array($issue->status, ['open', 'following'], true),
            422,
            'Hanya issue Open atau Following yang dapat ditutup.'
        );

        $issue->update([
            'status' => 'done',
            'tanggal_done' => now(),
            'done_by' => auth()->id(),
        ]);
        broadcast(new SmChanged($issue->id, 'status'))->toOthers();
        return back()->with('success', 'Issue berhasil ditutup oleh writer.');
    }

    public function destroy(MonitoringIssue $issue)
    {
        abort_unless($this->canEdit($issue), 403, 'Hanya writer dapat menghapus draft miliknya.');
        $id = $issue->id;
        $issue->delete();
        broadcast(new SmChanged($id, 'deleted'))->toOthers();
        return back()->with('success', 'Draft dihapus.');
    }


    public function messages(MonitoringIssue $issue)
    {
        abort_unless($this->canSee($issue), 403);

        $userId = (int) auth()->id();

        // Ambil seluruh pesan sesuai issue
        $messages = $issue->messages()
            ->with('sender:id,name')
            ->oldest('id')
            ->get();

        // Cari pesan dari user lain yang belum dibaca
        $unreadIds = $issue->messages()
            ->where('user_id', '!=', $userId)
            ->whereDoesntHave('reads', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->pluck('id');

        // Simpan status baca sesuai struktur tabel asli
        if ($unreadIds->isNotEmpty()) {
            $rows = $unreadIds->map(function ($messageId) use ($userId) {
                return [
                    'message_id' => $messageId,
                    'user_id' => $userId,
                    'read_at' => now(),
                ];
            });

            foreach ($rows->chunk(500) as $chunk) {
                DB::table('monitoring_issue_message_reads')
                    ->insertOrIgnore($chunk->all());
            }
        }

        return response()->json([
            'messages' => $messages->map(fn($m) => [
                'id' => $m->id,
                'parent_id' => $m->parent_id,
                'user' => $m->sender?->name ?? 'User',
                'mine' => (int) $m->user_id === $userId,
                'message' => $m->message,
                'attachment_name' => $m->attachment_name,
                'attachment_url' => $m->attachment_path
                    ? route('sm.attachments.download', [$issue, $m])
                    : null,
                'attachment_is_image' => $m->attachment_path
                    ? in_array(
                        strtolower(pathinfo(
                            $m->attachment_name ?? '',
                            PATHINFO_EXTENSION
                        )),
                        ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'],
                        true
                    )
                    : false,
                'time' => $m->created_at?->format('d/m/Y H:i'),
            ]),
            'unread_count' => 0,
        ]);
    }

    public function sendMessage(Request $request, MonitoringIssue $issue)
    {
        abort_unless($this->canSee($issue) && $issue->status !== 'draft', 403);
        $data = $request->validate([
            'message' => 'nullable|string|max:10000',
            'parent_id' => 'nullable|integer',
            'attachment' => 'nullable|file|max:20480|mimes:jpg,jpeg,png,gif,webp,bmp,pdf,doc,docx,xls,xlsx,csv,txt,zip,rar,7z,ppt,pptx',
        ]);
        if (!filled($data['message'] ?? null) && !$request->hasFile('attachment')) {
            return response()->json(['message' => 'Isi pesan atau pilih attachment.'], 422);
        }
        if (!empty($data['parent_id'])) {
            abort_unless(MonitoringIssueMessage::where('monitoring_issue_id', $issue->id)
                ->whereKey($data['parent_id'])->exists(), 422, 'Pesan balasan tidak valid.');
        }

        $attachmentPath = null;
        $attachmentName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = Str::limit(basename($file->getClientOriginalName()), 200, '');
            $attachmentPath = $file->store('sm-attachments/' . $issue->id, 'local');
        }
        try {
            $message = $issue->messages()->create([
                'user_id' => auth()->id(),
                'message' => $data['message'] ?? '',
                'parent_id' => $data['parent_id'] ?? null,
                'attachment_path' => $attachmentPath,
                'attachment_name' => $attachmentName,
            ]);
        } catch (\Throwable $e) {
            if ($attachmentPath)
                Storage::disk('local')->delete($attachmentPath);
            throw $e;
        }
        broadcast(new SmChanged($issue->id, 'message', ['message_id' => $message->id]))->toOthers();
        return response()->json(['ok' => true, 'id' => $message->id], 201);
    }

    public function downloadAttachment(MonitoringIssue $issue, MonitoringIssueMessage $message)
    {
        abort_unless($this->canSee($issue) && $issue->status !== 'draft', 403);
        abort_unless((int) $message->monitoring_issue_id === (int) $issue->id, 404);
        abort_unless($message->attachment_path && Storage::disk('local')->exists($message->attachment_path), 404);
        $name = $message->attachment_name ?: 'attachment';
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $mime = Storage::disk('local')->mimeType($message->attachment_path) ?: 'application/octet-stream';
        if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'], true) && str_starts_with($mime, 'image/')) {
            return response()->file(Storage::disk('local')->path($message->attachment_path), [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . str_replace('"', '', basename($name)) . '"',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }
        return Storage::disk('local')->download($message->attachment_path, $name, ['X-Content-Type-Options' => 'nosniff']);
    }
}
