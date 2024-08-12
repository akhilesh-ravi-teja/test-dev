<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'mail_logs';
    protected $fillable = ['customer_id', 'order_number', 'outlet_id', 'offer_id' ,'order_type','table_number','payment_type',  'total_amount','order_status','created_by'];
}
