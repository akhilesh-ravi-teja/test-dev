<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;

class Restaurant extends Model
{
    use HasFactory;
    protected $table = 'restaurants';
    
    protected $fillable = ['restaurant_name','location','address','logo','lat_long','payment_plan'];

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
