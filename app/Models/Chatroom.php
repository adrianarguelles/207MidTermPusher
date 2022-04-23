<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chatroom extends Model
{
    use HasFactory;

    protected $primaryKey = 'room_id';

    public function members() {
        return $this->belongsToMany(User::class, 'members', 'room_id', 'user_id')->withTimestamps();
    }
}
