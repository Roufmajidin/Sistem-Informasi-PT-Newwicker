@extends('master.master')
@section('content')
    <div class="container-fluid py-3" id="smPage">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h4 class="fw-bold mb-0">Sourcing Monitoring Issue</h4><small class="text-muted">Issue, follow-up Director &
                    realtime discussion</small>
            </div><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#smCreate">+ Create Issue</button>
        </div>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card p-3"><small>Total visible</small>
                    <h4>{{ $issues->total() }}</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3"><small>Open on page</small>
                    <h4>{{ $issues->getCollection()->where('status', 'open')->count() }}</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3"><small>Draft on page</small>
                    <h4>{{ $issues->getCollection()->where('status', 'draft')->count() }}</h4>
                </div>
            </div>
        </div>
        <form method="GET" action="{{ route('sm.index') }}" class="card card-body mb-3">
            <div class="row g-2">
                <div class="col-md-7"><input class="form-control" name="q" value="{{ request('q') }}"
                        placeholder="Cari problem / impact"></div>
                <div class="col-md-3"><select class="form-select" name="status">
                        <option value="">All status</option>
                        @foreach (['draft', 'open', 'following', 'done'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary flex-fill">Filter</button><a
                        class="btn btn-outline-secondary" href="{{ route('sm.index') }}">Reset</a></div>
            </div>
        </form>
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 sm-issue-table">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Report By</th>
                            <th>Problem</th>
                            <th>Potential Impact</th>
                            <th>Follow up Director</th>
                            <th>Comments</th>
                            <th>Status</th>
                            <th>Tanggal Done</th>
                            <th>Chat</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $isFactoryDirector = \Illuminate\Support\Str::startsWith(
                                strtolower(trim((string) (auth()->user()->email ?? ''))),
                                'factory@',
                            );
                        @endphp
                        @forelse($issues as $issue)
                            @php
                                $mine = (int) $issue->user_id === (int) auth()->id();
                                $director = $isFactoryDirector;
                            @endphp
                            <tr>
                                <td>{{ $issues->firstItem() + $loop->index }}</td>
                                <td>{{ $issue->reporter?->name ?? '-' }}<div class="small text-muted">
                                        {{ $issue->created_at?->format('d/m/Y') }}</div>
                                </td>
                                <td class="sm-points-cell">@include('pages.sm.partials.points', [
                                    'value' => $issue->problem,
                                    'key' => 'problem-' . $issue->id,
                                ])</td>
                                <td class="sm-points-cell">@include('pages.sm.partials.points', [
                                    'value' => $issue->potential_impact,
                                    'key' => 'impact-' . $issue->id,
                                ])</td>
                                <td>
                                    @if ($director && $issue->status !== 'draft')
                                        <button type="button" class="btn btn-link text-start p-0 sm-inline-edit"
                                            data-bs-toggle="modal" data-bs-target="#smDirector{{ $issue->id }}"
                                            title="Edit follow-up">
                                            @if ($issue->follow_director)
                                            <span class="sm-multiline">{{ $issue->follow_director }}</span>@else<span
                                                    class="text-muted fst-italic">Tap / write here to write</span>
                                            @endif
                                        </button>
                                    @else<span class="sm-multiline">{{ $issue->follow_director ?: '-' }}</span>
                                    @endif
                                </td>
                                <td class="sm-points-cell">
                                    @if ($issue->status !== 'draft')
                                        <button type="button" class="btn btn-link text-start p-0 sm-inline-edit"
                                            data-bs-toggle="modal" data-bs-target="#smComments{{ $issue->id }}"
                                            title="Isi atau edit Comments">
                                            @if (filled($issue->comments))
                                                <span class="sm-multiline">{{ $issue->comments }}</span>
                                            @else
                                                <span class="text-muted fst-italic">+ Write comments</span>
                                            @endif
                                        </button>
                                    @else
                                        <span class="sm-multiline">{{ $issue->comments ?: '-' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="badge bg-{{ $issue->status === 'done' ? 'success' : ($issue->status === 'following' ? 'info text-dark' : ($issue->status === 'open' ? 'warning text-dark' : 'secondary')) }}">{{ ucfirst($issue->status) }}</span>
                                </td>
                                <td>{{ $issue->tanggal_done?->format('d/m/Y H:i') ?: '-' }}</td>
                                <td><button type="button" class="btn btn-sm btn-outline-success sm-chat-btn"
                                        data-id="{{ $issue->id }}" data-title="{{ $issue->problem }}"
                                        @disabled($issue->status === 'draft')>Chat ({{ $issue->messages_count }})</button></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if ($mine && $issue->status === 'draft')
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#smEdit{{ $issue->id }}">Edit</button>
                                            <form method="POST" action="{{ route('sm.release', $issue) }}"
                                                onsubmit="return confirm('Release issue menjadi Open?')">@csrf<button
                                                    class="btn btn-sm btn-success">Release</button></form>
                                            <form method="POST" action="{{ route('sm.destroy', $issue) }}"
                                                onsubmit="return confirm('Hapus draft?')">@csrf @method('DELETE')<button
                                                    class="btn btn-sm btn-outline-danger">Delete</button></form>
                                        @endif
                                        @if ($mine && in_array($issue->status, ['open', 'following'], true))
                                            <form method="POST" action="{{ route('sm.close', $issue) }}"
                                                onsubmit="return confirm('Tutup issue ini sebagai Done?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Close Issue</button>
                                            </form>
                                        @endif
                                        @if ($director && $issue->status !== 'draft')
                                            <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal"
                                                data-bs-target="#smDirector{{ $issue->id }}">Director</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty<tr>
                                <td colspan="10" class="text-center text-muted p-4">Belum ada issue.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($issues->hasPages())
                <div class="card-footer">{{ $issues->links() }}</div>
            @endif
        </div>
        @include('pages.sm.modals.create')
        @foreach ($issues as $issue)
            @if ($issue->status !== 'draft')
                <div class="modal fade" id="smComments{{ $issue->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <form class="modal-content" method="POST" action="{{ route('sm.comments.update', $issue) }}">
                            @csrf
                            @method('PATCH')
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Comments — Issue #{{ $issue->id }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label" for="smCommentsInput{{ $issue->id }}">Comments</label>
                                <textarea id="smCommentsInput{{ $issue->id }}" name="comments" class="form-control sm-bullet-input"
                                    rows="6" maxlength="10000" placeholder="Tulis comments di sini...">{{ $issue->comments }}</textarea>
                                <small class="text-muted">Semua user yang dapat melihat issue ini dapat memperbarui
                                    Comments.</small>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Comments</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
            @if ((int) $issue->user_id === (int) auth()->id() && $issue->status === 'draft')
                @include('pages.sm.modals.edit', ['issue' => $issue])
            @endif
            @if ($isFactoryDirector && $issue->status !== 'draft')
                <div class="modal fade" id="smDirector{{ $issue->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <form class="modal-content" method="POST" action="{{ route('sm.director', $issue) }}">@csrf
                            @method('PUT')<div class="modal-header">
                                <h5 class="modal-title">Director Follow-up</h5><button class="btn-close" type="button"
                                    data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label">Follow-up Director</label>
                                <textarea class="form-control mb-3" name="follow_director" rows="4"
                                    placeholder="Tap / write here to write...">{{ $issue->follow_director }}</textarea>
                                <small class="text-muted">Status Done hanya dapat ditetapkan oleh writer pembuat
                                    issue.</small>
                            </div>
                            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endforeach
        <div class="modal fade" id="smChatModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered sm-chat-dialog">
                <div class="modal-content sm-chat-shell">
                    <div class="sm-chat-header">
                        <div class="d-flex align-items-center gap-3"><span class="sm-chat-avatar">SM</span>
                            <div class="min-width-0">
                                <h5 class="mb-0 text-truncate" id="smChatTitle">Issue Discussion</h5><small
                                    id="smRealtimeState" class="text-muted">Connecting...</small>
                            </div>
                        </div><button class="btn-close" type="button" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="sm-chat-content">
                        <aside class="sm-chat-sidebar">
                            <div class="sm-chat-sidehead"><strong>Issue Discussions</strong><small
                                    class="text-muted">Sourcing Monitoring</small></div>
                            <div id="smChatIssueList">
                                @foreach ($issues as $chatIssue)
                                    @if ($chatIssue->status !== 'draft')
                                        <button type="button" class="sm-chat-issue"
                                            data-issue-id="{{ $chatIssue->id }}"
                                            data-issue-title="{{ $chatIssue->problem }}"><span
                                                class="sm-chat-issue-icon">#</span><span
                                                class="sm-chat-issue-copy"><strong>{{ \Illuminate\Support\Str::limit($chatIssue->problem, 45) }}</strong><small>{{ strtoupper($chatIssue->status) }}
                                                    · {{ $chatIssue->messages_count }} messages</small></span></button>
                                    @endif
                                @endforeach
                            </div>
                        </aside>
                        <section class="sm-chat-main">
                            <div class="sm-chat-topic"><span class="sm-chat-dot"></span><span id="smChatTopic">Select
                                    issue to discuss</span><span class="ms-auto small text-muted"
                                    id="smTypingState"></span></div>
                            <div id="smChatMessages" class="sm-chat-messages"></div>
                            <div class="sm-chat-compose">
                                <div id="smReplyPreview" class="sm-reply-preview d-none"></div>
                                <div id="smAttachmentPreview" class="sm-attachment-preview d-none"></div>
                                <div class="d-flex gap-2 align-items-end"><input type="file" id="smAttachmentInput"
                                        class="d-none"
                                        accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip,.rar,.7z,.ppt,.pptx"><button
                                        id="smAttachButton" type="button"
                                        class="btn btn-outline-secondary sm-attach-btn" title="Attach file / image"
                                        aria-label="Attach file">📎</button>
                                    <textarea id="smChatInput" class="form-control" rows="2"
                                        placeholder="Type a message... (Enter to send, Shift+Enter new line)"></textarea><button id="smSend" type="button"
                                        class="btn btn-success sm-send-btn">➤</button>
                                </div><small class="text-muted">Live discussion · Attach files or paste images
                                    (Ctrl+V)</small>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        #smPage .sm-bubble {
            max-width: 82%;
            width: fit-content;
            padding: 9px 12px;
            border-radius: 10px;
            background: white;
            margin-bottom: 12px;
            box-shadow: 0 1px 2px #0001
        }

        #smPage .sm-mine {
            background: #d9fdd3;
            margin-left: auto
        }

        #smPage .sm-reply {
            border-left: 3px solid #409b6c;
            background: #0000000a;
            padding: 6px;
            margin: 5px 0;
            border-radius: 3px
        }

        #smPage .sm-chat-meta {
            font-size: 11px;
            color: #667;
            margin-top: 5px
        }

        #smPage .sm-reply-btn {
            border: 0;
            background: transparent;
            color: #317c58;
            font-size: 12px;
            cursor: pointer
        }

        @media(max-width:768px) {
            #smChatModal .modal-dialog {
                margin: 0;
                max-width: none
            }

            #smChatModal .modal-content {
                height: 100dvh;
                border-radius: 0
            }

            #smPage .sm-bubble {
                max-width: 93%
            }
        }

        #smPage .sm-multiline {
            white-space: pre-line
        }

        #smPage .sm-inline-edit {
            text-decoration: none;
            color: inherit;
            max-width: 280px;
            white-space: normal
        }

        #smPage .sm-inline-edit:hover {
            color: #0d6efd
        }

        #smPage .sm-issue-dialog {
            width: 92vw;
            max-width: 1100px
        }

        #smPage .sm-issue-dialog .modal-content {
            max-height: 92vh
        }

        #smPage .sm-issue-dialog .modal-footer {
            flex-shrink: 0
        }

        #smPage .sm-bullet-input {
            line-height: 1.65;
            resize: vertical
        }

        #smPage td {
            vertical-align: middle
        }

        @media(max-width:767px) {
            #smPage .sm-issue-dialog {
                width: auto;
                max-width: calc(100vw - 20px);
                margin: 10px auto
            }

            #smPage .sm-bullet-input {
                min-height: 140px
            }
        }

        #smPage .sm-chat-dialog {
            max-width: 1120px;
            width: 96vw
        }

        #smPage .sm-chat-shell {
            height: min(86vh, 800px);
            overflow: hidden;
            border-radius: 12px
        }

        #smPage .sm-chat-header {
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            background: #f7f9fa;
            border-bottom: 1px solid #e3e8e9
        }

        #smPage .sm-chat-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #d5e9e1;
            color: #176344;
            font-weight: 800
        }

        #smPage .sm-chat-content {
            display: flex;
            flex: 1;
            min-height: 0;
            height: calc(100% - 70px)
        }

        #smPage .sm-chat-sidebar {
            width: 310px;
            flex: none;
            background: #fff;
            border-right: 1px solid #e2e6e9;
            overflow-y: auto
        }

        #smPage .sm-chat-sidehead {
            display: flex;
            flex-direction: column;
            padding: 18px;
            border-bottom: 1px solid #eee
        }

        #smPage .sm-chat-issue {
            border: 0;
            border-bottom: 1px solid #f1f1f1;
            background: #fff;
            width: 100%;
            text-align: left;
            padding: 13px;
            display: flex;
            gap: 10px;
            align-items: center
        }

        #smPage .sm-chat-issue.active,
        #smPage .sm-chat-issue:hover {
            background: #eaf6f1
        }

        #smPage .sm-chat-issue-icon {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            background: #e5eaf0;
            border-radius: 50%;
            color: #426078;
            font-weight: 700
        }

        #smPage .sm-chat-issue-copy {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 3px
        }

        #smPage .sm-chat-issue-copy strong {
            font-size: 12px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap
        }

        #smPage .sm-chat-issue-copy small {
            font-size: 11px;
            color: #667
        }

        #smPage .sm-chat-main {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-width: 0;
            background: #e9e5dc
        }

        #smPage .sm-chat-topic {
            height: 44px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 16px;
            background: #f0f2f5;
            font-size: 12px;
            border-bottom: 1px solid #ddd
        }

        #smPage .sm-chat-dot {
            width: 8px;
            height: 8px;
            background: #23b36b;
            border-radius: 50%
        }

        #smPage .sm-chat-messages {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 18px 24px;
            background-color: #e9e5dc;
            background-image: radial-gradient(#d6d0c4 0.65px, transparent .65px);
            background-size: 18px 18px
        }

        #smPage .sm-chat-compose {
            padding: 12px 16px;
            background: #f0f2f5;
            border-top: 1px solid #ddd
        }

        #smPage .sm-chat-compose textarea {
            resize: none;
            border-radius: 12px
        }

        #smPage .sm-send-btn {
            height: 46px;
            min-width: 52px;
            border-radius: 12px
        }

        #smPage .sm-reply-preview {
            border-left: 4px solid #269c69;
            padding: 8px 12px;
            background: #fff;
            border-radius: 6px;
            margin-bottom: 8px
        }

        #smPage .sm-bubble {
            white-space: pre-wrap;
            overflow-wrap: anywhere
        }

        #smPage .sm-chat-meta {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: flex-end
        }

        #smPage .sm-chat-messages .sm-bubble {
            max-width: 76%
        }

        @media(max-width:767px) {
            #smPage .sm-chat-dialog {
                width: 100vw;
                max-width: none;
                margin: 0
            }

            #smPage .sm-chat-shell {
                height: 100dvh;
                border-radius: 0
            }

            #smPage .sm-chat-sidebar {
                display: none
            }

            #smPage .sm-chat-messages .sm-bubble {
                max-width: 93%
            }

            #smPage .sm-chat-header {
                height: 60px
            }

            #smPage .sm-chat-content {
                height: calc(100% - 60px)
            }
        }

        /* Compact expandable bullet list in issue table */
        #smPage .sm-issue-table td.sm-points-cell {
            min-width: 220px;
            max-width: 340px;
            vertical-align: top;
            white-space: normal
        }

        #smPage .sm-points {
            font-size: 12px;
            line-height: 1.55;
            overflow-wrap: anywhere
        }

        #smPage .sm-point {
            display: flex;
            align-items: flex-start;
            gap: 7px;
            margin: 0 0 5px
        }

        #smPage .sm-point-bullet {
            color: #2563eb;
            font-weight: 700;
            flex: none
        }

        #smPage .sm-point-text {
            min-width: 0;
            flex: 1;
            white-space: pre-wrap
        }

        #smPage .sm-point.is-hidden {
            display: none
        }

        #smPage .sm-points.is-expanded .sm-point.is-hidden {
            display: flex
        }

        #smPage .sm-points-toggle {
            border: 0;
            background: transparent;
            color: #2563eb;
            padding: 3px 0;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer
        }

        #smPage .sm-points-toggle:hover {
            text-decoration: underline
        }

        #smPage .sm-issue-table tbody td {
            vertical-align: top
        }

        #smPage .sm-attachment-preview {
            padding: 8px 12px;
            background: white;
            border-radius: 9px;
            margin-bottom: 8px;
            display: flex;
            gap: 12px;
            align-items: center;
            max-width: 100%;
            overflow-wrap: anywhere
        }

        #smPage .sm-attachment-preview.d-none {
            display: none !important
        }

        #smPage .sm-attachment-preview img {
            max-height: 85px;
            max-width: 120px;
            border-radius: 6px;
            object-fit: contain
        }

        #smPage .sm-attachment-media {
            display: block;
            max-width: min(100%, 320px);
            max-height: 290px;
            object-fit: contain;
            border-radius: 8px;
            margin: 6px 0;
            cursor: zoom-in
        }

        #smPage .sm-attachment-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ffffff80;
            padding: 8px;
            border-radius: 7px;
            margin-top: 6px;
            text-decoration: none;
            max-width: 100%;
            overflow-wrap: anywhere
        }

        #smPage .sm-attach-btn {
            height: 46px;
            min-width: 44px;
            border-radius: 10px
        }

        #smPage .sm-attachment-preview .btn {
            flex-shrink: 0
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.addEventListener('click', event => {
                const btn = event.target.closest('[data-sm-points-toggle]');
                if (!btn) return;
                const holder = btn.closest('[data-sm-points]');
                if (!holder) return;
                const expanded = holder.classList.toggle('is-expanded');
                btn.textContent = expanded ? btn.dataset.less : btn.dataset.more;
                btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            });
            // Bullet otomatis untuk Create & Edit. Native textarea, tidak mengubah format data.
            document.querySelectorAll('#smPage .sm-bullet-input').forEach(el => {
                el.addEventListener('keydown', e => {
                    if (e.ctrlKey || e.metaKey || e.altKey || e.isComposing) return;
                    const start = el.selectionStart,
                        end = el.selectionEnd,
                        val = el.value;
                    const lineStart = val.lastIndexOf('\n', start - 1) + 1;
                    const prefix = val.slice(lineStart, start);
                    if (e.key === ' ' && /^\s*-$/.test(prefix)) {
                        e.preventDefault();
                        el.setRangeText(prefix.replace(/-$/, '• '), lineStart, start, 'end');
                        el.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                    } else if (e.key === 'Enter' && /^\s*•\s*/.test(prefix)) {
                        e.preventDefault();
                        if (/^\s*•\s*$/.test(prefix)) el.setRangeText('', lineStart, start, 'end');
                        else el.setRangeText('\n' + prefix.match(/^\s*/)[0] + '• ', start, end,
                            'end');
                        el.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                    }
                });
            });
            const base = @json(url('/sm'));
            const csrf = @json(csrf_token());
            const currentUserId = @json(auth()->id());
            let issueId = null,
                replyId = null,
                items = [];
            let smChannel = null,
                smPusher = null,
                smSubscribed = false;
            let lastTyping = 0,
                typingTimeout = null;
            const seenMessageIds = new Set();
            const wrap = document.getElementById('smChatMessages'),
                input = document.getElementById('smChatInput'),
                preview = document.getElementById('smReplyPreview');
            const attachmentInput = document.getElementById('smAttachmentInput'),
                attachmentPreview = document.getElementById('smAttachmentPreview');
            let pendingAttachment = null,
                attachmentObjectUrl = null;
            const allowedExt = /\.(jpg|jpeg|png|gif|webp|bmp|pdf|doc|docx|xls|xlsx|csv|txt|zip|rar|7z|ppt|pptx)$/i;

            function clearAttachment() {
                pendingAttachment = null;
                attachmentInput.value = '';
                if (attachmentObjectUrl) URL.revokeObjectURL(attachmentObjectUrl);
                attachmentObjectUrl = null;
                attachmentPreview.innerHTML = '';
                attachmentPreview.classList.add('d-none');
            }

            function selectAttachment(file) {
                if (!file) return;
                if (file.size > 20 * 1024 * 1024) {
                    alert('Maksimal ukuran file 20 MB');
                    return;
                }
                if (!allowedExt.test(file.name)) {
                    alert('Format file tidak didukung');
                    return;
                }
                clearAttachment();
                pendingAttachment = file;
                const imageFile = /^image\/(png|jpeg|gif|webp|bmp)$/.test(file.type);
                attachmentObjectUrl = imageFile ? URL.createObjectURL(file) : null;
                attachmentPreview.innerHTML = (imageFile ? `<img src="${attachmentObjectUrl}" alt="Preview">` :
                        '📄') +
                    `<span class="flex-grow-1 small">${escapeHtml(file.name)}<br><span class="text-muted">${(file.size/1024/1024).toFixed(2)} MB</span></span><button type="button" class="btn btn-sm btn-outline-danger" id="smRemoveAttachment">✕</button>`;
                attachmentPreview.classList.remove('d-none');
                document.getElementById('smRemoveAttachment').onclick = clearAttachment;
            }
            document.getElementById('smAttachButton').addEventListener('click', () => attachmentInput.click());
            attachmentInput.addEventListener('change', () => selectAttachment(attachmentInput.files?.[0]));
            // Clipboard: Excel copied image, screenshot, or image copied from File Explorer (browser-dependent).
            document.addEventListener('paste', e => {
                if (!document.getElementById('smChatModal')?.classList.contains('show')) return;
                const files = [...(e.clipboardData?.files || [])];
                const image = files.find(f => f.type.startsWith('image/'));
                if (image) {
                    e.preventDefault();
                    const ext = image.type.split('/')[1] === 'jpeg' ? 'jpg' : image.type.split('/')[1];
                    selectAttachment(new File([image], `pasted-image-${Date.now()}.${ext}`, {
                        type: image.type
                    }));
                }
            });
            const escapeHtml = s => String(s ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));

            function render() {
                wrap.innerHTML = items.map(m => {
                    let p = items.find(x => x.id === m.parent_id);
                    let attachment = m.attachment_url ? (m.attachment_is_image ?
                        `<a href="${escapeHtml(m.attachment_url)}" target="_blank" rel="noopener"><img class="sm-attachment-media" src="${escapeHtml(m.attachment_url)}" alt="${escapeHtml(m.attachment_name)}" loading="lazy"></a>` :
                        `<a class="sm-attachment-link" href="${escapeHtml(m.attachment_url)}">📎 ${escapeHtml(m.attachment_name)}</a>`
                    ) : '';
                    return `<div class="sm-bubble ${m.mine?'sm-mine':''}"><strong class="small">${escapeHtml(m.user)}</strong>${p?`<div class="sm-reply">${escapeHtml(p.user)}: ${escapeHtml(p.message)}</div>`:''}<div>${escapeHtml(m.message)}</div>${attachment}<div class="sm-chat-meta">${escapeHtml(m.time)} <button class="sm-reply-btn" data-reply="${m.id}">↩ Reply</button></div></div>`
                }).join('') || '<div class="text-center text-muted">No messages yet</div>';
                wrap.scrollTop = wrap.scrollHeight;
            }
            async function load() {
                if (!issueId) return;
                const id = issueId;
                const res = await fetch(`${base}/${id}/messages`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) return;
                if (issueId !== id) return;
                items = (await res.json()).messages;
                items.forEach(m => seenMessageIds.add(Number(m.id)));
                render();
            }

            function openIssue(id, title) {
                issueId = Number(id);
                replyId = null;
                preview.classList.add('d-none');
                clearAttachment();
                document.getElementById('smChatTitle').textContent = title;
                document.getElementById('smChatTopic').textContent = title;
                document.querySelectorAll('.sm-chat-issue').forEach(el => el.classList.toggle('active', Number(el
                    .dataset.issueId) === issueId));
                load();
                const modal = document.getElementById('smChatModal');
                if (!modal.classList.contains('show')) {
                    if (window.bootstrap?.Modal) {
                        new bootstrap.Modal(modal).show();
                    } else if (window.jQuery?.fn?.modal) {
                        window.jQuery(modal).modal('show');
                    } else {
                        alert('Bootstrap Modal JS belum dimuat.');
                    }
                }
            }
            document.querySelectorAll('.sm-chat-btn').forEach(b => b.addEventListener('click', () => openIssue(b
                .dataset.id, b.dataset.title)));
            document.querySelectorAll('.sm-chat-issue').forEach(b => b.addEventListener('click', () => openIssue(b
                .dataset.issueId, b.dataset.issueTitle)));
            wrap.addEventListener('click', e => {
                let b = e.target.closest('[data-reply]');
                if (!b) return;
                replyId = Number(b.dataset.reply);
                let p = items.find(x => x.id === replyId);
                preview.classList.remove('d-none');
                preview.innerHTML =
                    `Reply to ${escapeHtml(p?.user)}: ${escapeHtml(p?.message)} <button type="button" class="btn btn-sm btn-link" id="smCancelReply">Cancel</button>`;
                document.getElementById('smCancelReply').onclick = () => {
                    replyId = null;
                    preview.classList.add('d-none')
                };
                input.focus()
            });
            async function send() {
                const message = input.value.trim();
                if (!issueId || (!message && !pendingAttachment)) return;
                const sendBtn = document.getElementById('smSend');
                sendBtn.disabled = true;
                const data = new FormData();
                data.append('message', message);
                if (replyId) data.append('parent_id', replyId);
                if (pendingAttachment) data.append('attachment', pendingAttachment, pendingAttachment.name);
                try {
                    const r = await fetch(`${base}/${issueId}/messages`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: data
                    });
                    if (!r.ok) {
                        const error = await r.json().catch(() => ({}));
                        alert(error.message || 'Failed to send attachment/message');
                        return;
                    }
                    const result = await r.json(),
                        sentId = issueId;
                    input.value = '';
                    replyId = null;
                    preview.classList.add('d-none');
                    clearAttachment();
                    await load();
                    if (smSubscribed && smChannel) {
                        try {
                            smChannel.trigger('client-sm-live-message', {
                                issue_id: sentId,
                                user_id: currentUserId,
                                message_id: result.id,
                                at: Date.now()
                            });
                        } catch (e) {
                            console.warn('[SM] Client event failed', e)
                        }
                    }
                } catch (e) {
                    console.error(e);
                    alert('Network error saat mengirim pesan');
                } finally {
                    sendBtn.disabled = false;
                }
            }
            input.addEventListener('input', () => {
                if (!smSubscribed || !smChannel || !issueId || Date.now() - lastTyping < 900) return;
                lastTyping = Date.now();
                try {
                    smChannel.trigger('client-sm-typing', {
                        issue_id: issueId,
                        user_id: currentUserId
                    })
                } catch (e) {
                    console.warn(e)
                }
            });
            document.getElementById('smSend').addEventListener('click', send);
            input.addEventListener('keydown', e => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    send()
                }
            });
            // Sama seperti modul Upah Transaksi: gunakan Pusher JS langsung, tanpa polling/Echo.
            // Server event: App\Events\SmChanged, broadcastAs(): sm.changed, channel: private-sm.
            function smConnectionStatus(label, cls) {
                const el = document.getElementById('smRealtimeState');
                if (el) {
                    el.textContent = label;
                    el.className = 'small ' + cls;
                }
            }

            function connectSmPusher() {
                if (window.__smRealtimeConnected) return;
                const key = @json(config('broadcasting.connections.pusher.key'));
                const cluster = @json(config('broadcasting.connections.pusher.options.cluster'));
                const authEndpoint = @json(\Illuminate\Support\Facades\Route::has('pusher.auth') ? route('pusher.auth') : url('/broadcasting/auth'));
                if (!key || typeof window.Pusher === 'undefined') {
                    smConnectionStatus('Pusher unavailable', 'text-danger');
                    console.error('[SM] Pusher key/library missing');
                    return;
                }
                window.__smRealtimeConnected = true;
                smPusher = new window.Pusher(key, {
                    cluster: cluster || 'ap1',
                    forceTLS: true,
                    authEndpoint,
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        }
                    }
                });
                window.smRealtimePusher = smPusher;
                smChannel = smPusher.subscribe('private-sm');
                window.smRealtimeChannel = smChannel;
                smChannel.bind('pusher:subscription_succeeded', () => {
                    smSubscribed = true;
                    smConnectionStatus('● Live · Pusher', 'text-success');
                    console.info('[SM] Pusher subscription READY', smChannel.name);
                });
                smChannel.bind('pusher:subscription_error', err => {
                    smSubscribed = false;
                    smConnectionStatus('Pusher auth failed', 'text-danger');
                    console.error('[SM] Channel auth failed', err);
                });
                smPusher.connection.bind('state_change', states => {
                    if (states.current === 'disconnected' || states.current === 'unavailable') {
                        smSubscribed = false;
                        smConnectionStatus('Pusher disconnected', 'text-danger');
                    }
                });
                // Pola yang sama dengan Upah: client event pada private channel setelah POST sukses.
                // Server event tetap aktif sebagai jalur utama untuk update lintas halaman.
                smChannel.bind('client-sm-live-message', data => {
                    if (Number(data.user_id) === Number(currentUserId)) return;
                    console.info('[SM] Client message received', data);
                    if (Number(data.issue_id) === Number(issueId)) load();
                    if (data.message_id && seenMessageIds.has(Number(data.message_id))) return;
                    if (data.message_id) seenMessageIds.add(Number(data.message_id));
                    const btn = document.querySelector('.sm-chat-btn[data-id="' + Number(data.issue_id) +
                        '"]');
                    if (btn) {
                        const m = btn.textContent.match(/\((\d+)\)/);
                        if (m) btn.textContent = btn.textContent.replace(/\(\d+\)/, '(' + (Number(m[1]) +
                            1) + ')');
                    }
                });
                smChannel.bind('client-sm-typing', data => {
                    if (Number(data.user_id) === Number(currentUserId) || Number(data.issue_id) !== Number(
                            issueId)) return;
                    const el = document.getElementById('smTypingState');
                    el.textContent = 'Someone is typing...';
                    clearTimeout(typingTimeout);
                    typingTimeout = setTimeout(() => el.textContent = '', 1500);
                });
                smChannel.bind('sm.changed', event => {
                    if (event.action === 'message') {
                        if (event.payload?.message_id && seenMessageIds.has(Number(event.payload
                                .message_id))) return;
                        if (event.payload?.message_id) seenMessageIds.add(Number(event.payload.message_id));
                        if (Number(event.issue_id) === Number(issueId)) load();
                        const btn = document.querySelector('.sm-chat-btn[data-id="' + Number(event
                            .issue_id) + '"]');
                        if (btn) {
                            const m = btn.textContent.match(/\((\d+)\)/);
                            if (m) btn.textContent = btn.textContent.replace(/\(\d+\)/, '(' + (Number(m[
                                1]) + 1) + ')');
                        }
                    } else {
                        // Issue Release / status / follow-up: perbarui tabel untuk user lain.
                        if (!document.getElementById('smChatModal').classList.contains('show')) window
                            .location.reload();
                    }
                });
            }
            if (typeof window.Pusher !== 'undefined') connectSmPusher();
            else {
                const script = document.createElement('script');
                script.src = 'https\://js.pusher.com/8.4.0/pusher.min.js';
                script.onload = connectSmPusher;
                script.onerror = () => smConnectionStatus('Pusher script failed', 'text-danger');
                document.head.appendChild(script);
            }
        });
    </script>
@endsection
