<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class message extends Model
{
    protected $table = "messages";
    protected $primaryKey = "message_id";
    protected $fillable = ['chat_room_id','user_id', 'message'];
}
