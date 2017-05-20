<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class mobile_user extends Model
{
    protected $table = "mobile_user";
    protected $primaryKey = "id";
    protected $fillable = ['name', 'email', 'firebaseID', 'avatar_link', 'password'];

    public function report_news(){
    	$this->belongsTo(report_posts::class, 'user_ID', 'id');
    }
}
