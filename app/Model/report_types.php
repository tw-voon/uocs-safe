<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class report_types extends Model
{
    protected $table = "report_type";
    protected $primaryKey = "id";
    protected $fillable = ['typeName', 'isAutoReport'];

    public function reporttype(){
    	return $this->belongsTo(report_posts::class, 'type_ID', 'id');
    }

    public function type(){
    	return $this->hasMany(report_posts::class, 'type_ID', 'id');
    }
}
