<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class approve_handler extends Model
{
    protected $table = "approve_handler";
    protected $primaryKey = "handler_id";
    protected $fillable = ['report_id', 'status_id', 'reason', 'action_taken'];
}
