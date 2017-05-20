<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class tip_categories extends Model
{
    protected $table = "tips_category";
    protected $primaryKey = "id";
    protected $fillable = ['category_name'];
}
