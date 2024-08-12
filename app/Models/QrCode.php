<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;

class QrCode extends Model
{
    use HasFactory;
    protected $table = 'qr_codes';
    protected $fillable = ['qr_code_path','table_id'];

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }
}
