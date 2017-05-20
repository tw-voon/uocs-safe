<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class safety_tips extends Model
{
    protected $table = "safetytips";
    protected $primaryKey = "id";
    protected $fillable = ['category_id', 'tip_name', 'tip_desc', 'tip_status'];
}
