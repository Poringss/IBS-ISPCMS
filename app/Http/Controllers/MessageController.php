<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Conversations list — shows all users the current user can chat with.
     *
     * Rules:
     *   • Client  → can only DM admin(s)
     *   • Admin   → can DM staff and clients (and other admins)
     *   • Staff   → can DM admin(s) and other staff
     */
    public function index()
    {
        $user = Auth::user();

        // Build the list of users this person is allowed to message
        $contactsQuery = User::where('id', '!=', $user->id);

        if ($user->isClient()) {
            // Clients can only message admins
            $contactsQuery->where('role', 'admin');
        } elseif ($user->isStaff()) {
            // Staff can message admins + other staff
            $contactsQuery->whereIn('role', ['admin', 'staff']);
        }
        // Admin sees everyone (no extra filter)

        $contacts = $contactsQuery->orderBy('name')->get();

        // Get last message + unread count for each contact
        $conversations = $contacts->map(function ($contact) use ($user) {
            $lastMessage = Message::where(function ($q) use ($user, $contact) {
                    $q->where('sender_id', $user->id)->where('receiver_id', $contact->id);
                })
                ->orWhere(function ($q) use ($user, $contact) {
                    $q->where('sender_id', $contact->id)->where('receiver_id', $user->id);
                })
                ->orderByDesc('created_at')
                ->first();

            $unread = Message::where('sender_id', $contact->id)
                ->where('receiver_id', $user->id)
                ->whereNull('read_at')
                ->count();

            return (object) [
                'contact'      => $contact,
                'last_message' => $lastMessage,
                'unread'       => $unread,
            ];
        })->sortByDesc(function ($c) {
            return optional($c->last_message)->created_at ?? now()->subYears(10);
        })->values();

        $totalUnread = $conversations->sum('unread');

        return view('messages.index', compact('conversations', 'totalUnread'));
    }

    /**
     * Show the chat thread with a specific user.
     */
    public function chat(User $contact)
    {
        $user = Auth::user();

        // Enforce permission
        if ($user->isClient() && !$contact->isAdmin()) {
            abort(403, 'You can only message administrators.');
        }
        if ($user->isStaff() && $contact->isClient()) {
            abort(403, 'Staff cannot directly message clients.');
        }

        // Fetch messages between the two users
        $messages = Message::where(function ($q) use ($user, $contact) {
                $q->where('sender_id', $user->id)->where('receiver_id', $contact->id);
            })
            ->orWhere(function ($q) use ($user, $contact) {
                $q->where('sender_id', $contact->id)->where('receiver_id', $user->id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark received messages as read
        Message::where('sender_id', $contact->id)
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('messages.chat', compact('contact', 'messages'));
    }

    /**
     * Send a message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body'        => 'required|string|max:5000',
        ]);

        $user     = Auth::user();
        $receiver = User::findOrFail($request->receiver_id);

        // Enforce permission
        if ($user->isClient() && !$receiver->isAdmin()) {
            abort(403, 'You can only message administrators.');
        }
        if ($user->isStaff() && $receiver->isClient()) {
            abort(403, 'Staff cannot directly message clients.');
        }

        Message::create([
            'sender_id'   => $user->id,
            'receiver_id' => $receiver->id,
            'body'        => $request->body,
        ]);

        return redirect()->route('messages.chat', $receiver->id);
    }

    /**
     * Return unread count as JSON (for navbar badge polling).
     */
    public function unreadCount()
    {
        $count = Message::where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread' => $count]);
    }
}
