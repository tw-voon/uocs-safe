<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class locations extends Model
{
    protected $table = "location";
    protected $primaryKey = "id";
    protected $fillable = ['location_name', 'location_latitute', 'location_longitute'];

    public function location_id(){
    	$this->belongsTo(report_posts::class, 'location_ID', 'id');
    }
}
