<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class PersonalAccessTokens extends Model
{
    use HasFactory;
    protected $table = 'personal_access_tokens';
    protected $fillable = ['tokenable_type','tokenable_id','name','token','abilities','expires_at','last_used_at','status'];

}
