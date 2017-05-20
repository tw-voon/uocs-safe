<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class status_table extends Model
{
    protected $table = "status_table";
    protected $primaryKey = "id";
    protected $fillable = ['name'];

    public function report_status(){
    	$this->belongsTo(report_posts::class, 'approve_status', 'id');
    }
}
