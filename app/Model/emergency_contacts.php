<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class emergency_contacts extends Model
{
    
    protected $table = "emergency_contact";
    protected $primaryKey = "id";
    protected $fillable = ['contact_name', 'contact_description', 'contact_number'];
    
}
