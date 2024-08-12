<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'orders';
    protected $fillable = ['customer_id', 'order_number', 'outlet_id', 'offer_id' ,'order_type','table_number','payment_type','total_tax','total_amount','order_status','created_by','total_subtotal'];
    
    public function countAllOrders($outletId = null)
    {
        $query = self::query();

        if ($outletId !== null) {
            $query->where('outlet_id', $outletId);
        }

        return $query->count();
    }

    public function orderItems(){
        return $this->hasMany(\App\Models\OrderItem::class,'order_id');
    }

}
