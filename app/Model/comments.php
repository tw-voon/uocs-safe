<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class comments extends Model
{
    protected $table = "comments";
    protected $primaryKey = "id";
    protected $fillable = ['user_id', 'report_id', 'comment_msg'];
}
