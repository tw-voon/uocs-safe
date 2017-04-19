<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class chat_handler extends Model
{
    protected $table = "chat_handler";
    protected $primaryKey = "handler_id";
    protected $fillable = ['user_id', 'target_user_id', 'chat_room_id'];
}
