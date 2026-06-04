<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    // ── Page ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $me   = Auth::user();
        $convs = Conversation::whereHas('participants', fn($q) => $q->where('user_id', $me->id))
            ->with(['users', 'lastMessage.user',
                    'participants' => fn($q) => $q->where('user_id', $me->id)])
            ->get()
            ->map(function ($conv) use ($me) {
                $part   = $conv->participants->first();
                $lastId = $part?->last_read_message_id ?? 0;
                $unread = Message::where('conversation_id', $conv->id)
                    ->where('id', '>', $lastId)
                    ->where('user_id', '!=', $me->id)
                    ->count();
                return [
                    'conv'        => $conv,
                    'displayName' => $conv->displayName($me),
                    'lastMsg'     => $conv->lastMessage,
                    'unread'      => $unread,
                ];
            })
            ->sortByDesc(fn($c) => $c['lastMsg']?->created_at)
            ->values();

        $users = User::where('id', '!=', $me->id)->where('is_active', true)->orderBy('name')->get();

        return view('chat.index', compact('convs', 'users'));
    }

    // ── Start / create ────────────────────────────────────────────────────────

    public function startDirect(User $user)
    {
        $me = Auth::user();

        $conv = Conversation::where('type', 'direct')
            ->whereHas('participants', fn($q) => $q->where('user_id', $me->id))
            ->whereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->first();

        if (!$conv) {
            $conv = Conversation::create(['type' => 'direct', 'created_by_id' => $me->id]);
            $conv->participants()->createMany([
                ['user_id' => $me->id],
                ['user_id' => $user->id],
            ]);
        }

        return response()->json([
            'id'          => $conv->id,
            'displayName' => $user->name,
            'isGroup'     => false,
        ]);
    }

    public function createGroup(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $me   = Auth::user();
        $conv = Conversation::create([
            'name'          => $request->name,
            'type'          => 'group',
            'created_by_id' => $me->id,
        ]);

        $ids = array_unique(array_merge([$me->id], $request->user_ids));
        $conv->participants()->createMany(array_map(fn($id) => ['user_id' => $id], $ids));

        return response()->json([
            'id'          => $conv->id,
            'displayName' => $conv->name,
            'isGroup'     => true,
        ]);
    }

    // ── Messages ──────────────────────────────────────────────────────────────

    public function fetch(Request $request, Conversation $conversation)
    {
        $me   = Auth::user();
        $part = $conversation->participants()->where('user_id', $me->id)->first();
        abort_if(!$part, 403);

        $afterId = $request->query('after');

        $query = Message::where('conversation_id', $conversation->id)->with('user');

        $messages = $afterId !== null
            ? $query->where('id', '>', $afterId)->orderBy('id')->get()
            : $query->orderBy('id', 'desc')->limit(60)->get()->reverse()->values();

        // Mark as read
        $latest = $conversation->messages()->max('id') ?? 0;
        $part->last_read_message_id = $latest;
        $part->save();

        return response()->json($messages->map(fn($m) => $this->fmt($m, $me)));
    }

    public function send(Request $request, Conversation $conversation)
    {
        $me   = Auth::user();
        $part = $conversation->participants()->where('user_id', $me->id)->first();
        abort_if(!$part, 403);

        $request->validate([
            'body'       => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm|max:51200',
        ]);

        if (!$request->filled('body') && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Mesaj boş ola bilməz'], 422);
        }

        $path = $type = $name = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $type = str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'video';
            $name = $file->getClientOriginalName();
            $path = $file->store('chat', 'public');
        }

        $msg = Message::create([
            'conversation_id' => $conversation->id,
            'user_id'         => $me->id,
            'body'            => $request->filled('body') ? $request->body : null,
            'attachment_path' => $path,
            'attachment_type' => $type,
            'attachment_name' => $name,
        ]);

        $part->last_read_message_id = $msg->id;
        $part->save();

        $msg->load('user');
        return response()->json($this->fmt($msg, $me));
    }

    // ── Unread count ──────────────────────────────────────────────────────────

    public function unreadCount()
    {
        $me    = Auth::id();
        $count = DB::table('messages')
            ->join('conversation_participants as cp',
                fn($j) => $j->on('messages.conversation_id', '=', 'cp.conversation_id')
                             ->where('cp.user_id', $me))
            ->whereColumn('messages.id', '>', 'cp.last_read_message_id')
            ->where('messages.user_id', '!=', $me)
            ->count();

        return response()->json(['count' => $count]);
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function fmt(Message $m, User $me): array
    {
        return [
            'id'              => $m->id,
            'body'            => $m->body,
            'attachment_url'  => $m->attachmentUrl(),
            'attachment_type' => $m->attachment_type,
            'attachment_name' => $m->attachment_name,
            'time'            => $m->created_at->format('H:i'),
            'date'            => $m->created_at->format('d.m.Y'),
            'mine'            => $m->user_id === $me->id,
            'sender_name'     => $m->user->name,
            'sender_initial'  => strtoupper(substr($m->user->name, 0, 1)),
        ];
    }
}
