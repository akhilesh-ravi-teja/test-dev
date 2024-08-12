<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class MailLog extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'mail_logs';
    protected $fillable = ['user_id','email','subject','mail_body','error','is_read','status' ];
}
