<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;


class Table extends Model
{
    use HasFactory;
    protected $table = 'tables';
    protected $fillable = ['outlet_id','table_number','table_status'];

    public function qrCode()
    {
        return $this->hasOne(\App\Models\QrCode::class, 'table_id');
    }
}
