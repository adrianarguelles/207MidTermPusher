<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class ChatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('chats');
    }

    public function fetchMessages()
    {
        return Message::with('user')->get();
    }

    public function sendMessage(Request $request)
    {
        // Check if the message has an attachment
        if ($request->has('attachment')) {
            $attachmentPath = $request->file('attachment')->store('images', 'public');

            // Save message with attachment
            $message = auth()->user()->messages()->create([
                'message' => $request->input('message', ''),
                'sent_at' => now(),
                'attachment_path' => asset($attachmentPath)
            ]);

            broadcast(new \App\Events\Message(
                $message->user->name, 
                $message->message,
                $message->attachment_path))->toOthers();

            return ['status' => 'success', 'imageUrl' => asset($attachmentPath) ];
        }
        else {
            // Save the message under the sending user
            $message = auth()->user()->messages()->create([
                'message' => $request->input('message'),
                'sent_at' => now()
            ]);

        }

        broadcast(new \App\Events\Message(
            $message->user->name, 
            $message->message, null))->toOthers();

        return ['status' => 'success'];
    }
}
