<?php 

namespace App\Validation;

use Illuminate\Support\Facades\Validator;
use App\Enums\OrderStatus;
use App\Enums\PaymentType;
use Illuminate\Validation\Rules\Enum;

class CreateOrderValidator
{
    public static function validate(array $input)
    {
        $validator = Validator::make($input,[
            'outlet_id' => 'sometimes|integer|exists:outlets,outlet_id',
            'customer_name' => 'sometimes|string|max:255',
            'customer_mobile' => 'sometimes|string|regex:/^\d{10}$/',
            'customer_email' => 'sometimes|email',
            'order_type'=>'sometimes',
            'customer_id'=>'sometimes',
            'order_id'=>'sometimes',
            'table_number'=>'sometimes',
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'order_status' => [
                'required',
                new Enum(OrderStatus::class),
            ],
            'payment_type'=>[
                'required',
                new Enum(PaymentType::class)],
        ]);

        // Add additional validation rules here if needed

        return $validator;
    }
}
