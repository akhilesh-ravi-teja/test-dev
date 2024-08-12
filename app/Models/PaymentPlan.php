<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class PaymentPlan extends Model
{
    use HasFactory;
    protected $table = 'payment_plans';
}
