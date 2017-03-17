<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class chat_rooms extends Model
{
    protected $table = "chat_rooms";
    protected $primaryKey = "chat_room_id";
    protected $fillable = ['name'];
}
