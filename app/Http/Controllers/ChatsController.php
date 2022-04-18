<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
//for the chatrooms
use App\Models\Chatroom;
use App\Models\Members;
use App\Events\MessageSent;
use App\Models\User;

/*MessageSent event version
-the entire message object gets passed which includes everything including room id 
and attachment url
*/
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
        //original
        //return Message::with('user')->get();
        // chatrooms and messages in the room
        $chatrooms = Chatroom::leftJoin('members','members.room_id', '=', 'chatrooms.room_id')
        ->where('members.user_id', auth()->user()->id)
        ->get();
        if(count($chatrooms)){
            $roomMsgs;
            foreach($chatrooms as $result){
                $msgs = Message::with('user')->where('room_id',$result->room_id)->get();
                $roomMsgs[] = array('room_id'=> $result->room_id, 'room_name'=>$result->room_name, 'messages'=> $msgs);
            }
            //return Message::with('user')->get();
            return $roomMsgs;  
        }else{
            return false;
        }

      
    }

    public function sendMessage(Request $request)
    {
        // Check if the message has an attachment
        if ($request->has('attachment')) {
            $attachmentPath = $request->file('attachment')->store('images', 'public');

            // Save message with attachment
            $message = auth()->user()->messages()->create([
                'message' => $request->input('message', ''),
                //try to include room_id
                'room_id' => $request->room_id,
                'sent_at' => now(),
                'attachment_path' => asset($attachmentPath)
            ]);

            //use incase room_id isn't getting inserted
            /*
            $message = new Message;
            $message->message = $request->message;
            $message->sender_id = auth()->user()->id;
            $message->room_id = $request->room_id;
            $message->attachment_path => asset($attachmentPath);
            $message->save();            
            */            

            /*MessageSent event version takes the entire message obj
            broadcast(new \App\Events\Message(
                $message->user->name, 
                $message->message,
                $message->attachment_path))->toOthers();
            */
            broadcast(new MessageSent($message->load('user')))->toOthers();

            return ['status' => 'success', 'imageUrl' => asset($attachmentPath) ];
        }
        else {
            // Save the message under the sending user and active room
            $message = auth()->user()->messages()->create([
                'message' => $request->input('message'),
                'room_id' => $request->room_id,                
                'sent_at' => now()
            ]);
            
            broadcast(new \App\Events\Message(
                $message->user->name,
                $message->room_id,
                $message->message, null))->toOthers();
            
                        
            //broadcast(new MessageSent($message->load('user')))->toOthers();
            return ['status' => 'success'];
        }



    }
    public function fetchChatrooms(){
        $results = Chatroom::leftJoin('members','members.room_id', '=', 'chatrooms.room_id')
        ->where('members.user_id', auth()->user()->id)->get();
        return $results;
    }    

    public function addRoom(Request $request){
        $newRoom = new Chatroom;
        $newRoom->room_name = $request->room_name;
        $newRoom->save();
        $firstMember = new Members;
        $firstMember->room_id = $newRoom->id;
        $firstMember->user_id = auth()->user()->id;
        $firstMember->save();
        return $newRoom;
    }

    public function addMember(Request $request){
        $user = User::where('email',$request->email)->get();
        
        $members = new Members;
        $members->room_id = $request->room_id;
        $members->user_id = $user[0]->id;
        $members->save();
        return ['status' => 'success'];
    }

    public function test(){
        $user = User::where('email','test2@test.com')->get();
        echo '<pre>';
        var_dump($user[0]->id);
        echo '</pre>';
    }
}
