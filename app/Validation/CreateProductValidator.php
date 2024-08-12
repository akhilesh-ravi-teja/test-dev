<?php 

namespace App\Validation;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateProductValidator
{
    public static function validateNewProduct(array $input)
    {
        $validator = Validator::make($input,[
            'outlet_id' => 'required',
            'category_id' => 'required',
            'product_data.*.product_name' => 'required',
            'product_data.*.product_type' => ['required', Rule::in(['veg', 'nonveg', 'vegan'])],
            'product_data.*.product_description' => 'required',
            'product_data.*.product_image' => 'sometimes',
            'product_data.*.product_price' => 'required|numeric',
        ]);

        // Add additional validation rules here if needed

        return $validator;
    }

    public static function validateForProductUpdate(array $input){
        $validator = Validator::make($input,[
            'outlet_id' => 'required|exists:outlets,outlet_id',
            'product_id'=>'required|exists:products,id',
            'product_name'=>'sometimes',
            'product_type'=>'sometimes',
            'product_description'=>'sometimes',
            'product_image'=>'sometimes',
            'product_price'=>'sometimes',
        ]);

        return $validator;
    }
}
