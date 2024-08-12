<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;


class OTP extends Model
{
    use HasFactory;
    protected $table = 'otp';
    protected $fillable = ['code','user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
