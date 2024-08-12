<?php 
namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class CustomerService
{
    
    public function createCustomerService($data){
        $customer = \App\Models\Customer::updateOrCreate(
            ['id' => $data['customer_id'] ?? ''],
            [
                'customer_name' => $data['customer_name'] ?? '',
                'customer_phone_number' => $data['customer_mobile'] ?? '',
                'customer_email' => $data['customer_email'] ?? '',
                'created_by' => Auth::user()->id,
            ]
        );

        return $customer;
    }

    public function getCustomer($data){
        
    }

}
