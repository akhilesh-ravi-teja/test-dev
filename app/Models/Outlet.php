<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Outlet extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'outlets';
    protected $primaryKey = 'outlet_id'; 
    protected $fillable = [
        'user_id', 'outlet_name','location', 'logo', 'address', 'latlong'
    ];
}
