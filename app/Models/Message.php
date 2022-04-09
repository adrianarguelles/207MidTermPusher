<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['message', 'sent_at', 'sender_id', 'attachment_path'];

    public function user()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
