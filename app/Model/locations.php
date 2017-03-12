<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class locations extends Model
{
    protected $table = "location";
    protected $primaryKey = "id";
    protected $fillable = ['location_name', 'location_latitute', 'location_longitute'];
}
