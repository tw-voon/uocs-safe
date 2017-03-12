<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class report_types extends Model
{
    protected $table = "report_type";
    protected $primaryKey = "reportID";
    protected $fillable = ['typeName', 'isAutoReport'];
}
