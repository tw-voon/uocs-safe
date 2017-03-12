<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class report_posts extends Model
{
    protected $table = "report";
    protected $primaryKey = "report_ID";
    protected $fillable = ['user_ID', 'type_ID', 'approve_ID', 'approve_status', 'report_Title', 'report_Description', 'image'];
}
