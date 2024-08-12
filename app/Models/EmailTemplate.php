<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class EmailTemplate extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'email_template';
    protected $fillable = ['user_id','outlet_id','subject','mail_body','mail_from','name_from','ack_email','status'];
}
