<?php 

namespace App\Validation;

use Illuminate\Support\Facades\Validator;

class CreateCustomerValidator
{
    public static function validate(array $input)
    {
        $validator = Validator::make($input, [
            'customer_name' => 'required|string|max:255',
            'customer_mobile' => 'sometimes|string|regex:/^\d{10}$/',
            'customer_email' => 'sometimes|email',
            'customer_id' => 'sometimes|int',
        ]);

        // Add additional validation rules here if needed

        return $validator;
    }
}
