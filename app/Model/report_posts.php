<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\report_types;
use App\Model\locations;

class report_posts extends Model
{
    protected $table = "report";
    protected $primaryKey = "id";
    protected $fillable = ['user_ID', 'type_ID', 'approve_ID', 'approve_status', 'report_Title', 'report_Description', 'image', 'location_ID'];

    // public function rows()
    // {
    //     return $this->belongsTo(report_types::class, 'type_ID')->orderBy('order');
    // }

    // public function reportId(){
    // 	return $this->hasOne('App\Model\approve_handler', 'id', 'handler_id');
    // }

    // public function typeId(){
    // 	return $this->belongsTo(report_types::class);
    // }

    public function approve(){
        return $this->hasOne(status_table::class, 'id', 'approve_status');
    }

    public function location(){
    	return $this->hasOne(locations::class, 'id', 'location_ID');
    }

    public function mobileuser(){
        return $this->hasOne(mobile_user::class, 'id', 'user_ID');
    }

    public function handler(){
        return $this->hasOne(approve_handler::class, 'report_id', 'id');
    }

    public function category(){
        return $this->hasOne(report_types::class, 'id', 'type_ID');
    }

    public function autoReport(){
        return $this->category()->where('isAutoReport', 1);
    }
}
