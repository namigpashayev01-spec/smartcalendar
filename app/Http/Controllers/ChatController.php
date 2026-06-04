<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $me    = Auth::user();
        $users = User::where('id', '!=', $me->id)
                     ->where('is_active', true)
                     ->orderBy('name')
                     ->get();

        $conversations = $users->map(function ($user) use ($me) {
            $last = Message::where(function ($q) use ($me, $user) {
                $q->where('from_user_id', $me->id)->where('to_user_id', $user->id);
            })->orWhere(function ($q) use ($me, $user) {
                $q->where('from_user_id', $user->id)->where('to_user_id', $me->id);
            })->latest()->first();

            $unread = Message::where('from_user_id', $user->id)
                ->where('to_user_id', $me->id)
                ->whereNull('read_at')
                ->count();

            return ['user' => $user, 'last' => $last, 'unread' => $unread];
        })->sortByDesc(fn($c) => $c['last']?->created_at)->values();

        return view('chat.index', compact('conversations'));
    }

    public function fetch(Request $request, User $user)
    {
        $me      = Auth::user();
        $afterId = $request->query('after');

        $base = Message::where(function ($q) use ($me, $user) {
            $q->where(function ($q2) use ($me, $user) {
                $q2->where('from_user_id', $me->id)->where('to_user_id', $user->id);
            })->orWhere(function ($q2) use ($me, $user) {
                $q2->where('from_user_id', $user->id)->where('to_user_id', $me->id);
            });
        });

        if ($afterId !== null) {
            $messages = (clone $base)->where('id', '>', $afterId)->orderBy('id')->get();
        } else {
            $messages = (clone $base)->orderBy('id', 'desc')->limit(60)->get()->reverse()->values();
        }

        // Mark incoming messages as read
        Message::where('from_user_id', $user->id)
            ->where('to_user_id', $me->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(
            $messages->map(fn($m) => [
                'id'   => $m->id,
                'body' => $m->body,
                'time' => $m->created_at->format('H:i'),
                'date' => $m->created_at->format('d.m.Y'),
                'mine' => $m->from_user_id === $me->id,
            ])
        );
    }

    public function send(Request $request, User $user)
    {
        $request->validate(['body' => 'required|string|max:2000']);

        $msg = Message::create([
            'from_user_id' => Auth::id(),
            'to_user_id'   => $user->id,
            'body'         => $request->body,
        ]);

        return response()->json([
            'id'   => $msg->id,
            'body' => $msg->body,
            'time' => $msg->created_at->format('H:i'),
            'date' => $msg->created_at->format('d.m.Y'),
            'mine' => true,
        ]);
    }

    public function unreadCount()
    {
        $count = Message::where('to_user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }
}
