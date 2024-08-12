<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Validation\CreateCustomerValidator;
use App\Services\CustomerService;

class CustomerController extends BaseController
{
    public function createCustomer(CustomerService $customer,Request $request)
    {

        try {
            $input = $request->all();

            $validator = CreateCustomerValidator::validate($input);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            //Check if the 'customer_mobile' or 'customer_email' fields are present and validate them
            $validator->sometimes('customer_mobile', 'required|string|regex:/^\d{10}$/', function ($input) {
                return !empty($input->customer_mobile);
            });

            $validator->sometimes('customer_email', 'required|email', function ($input) {
                return !empty($input->customer_email);
            });

            $validator->sometimes('customer_id', 'required|int', function ($input) {
                return !empty($input->customer_id);
            });

            $data = $validator->validated();
            $customer =  $customer->createCustomerService($data);
            return $this->sendResponse($customer,'success');
        } catch (Exception $e) {
            return $this->sendError('error', 'Internal Serve Error' . $e->getMessage(), 500);
        }
    }

    public function getCustomer(Request $request){
        try {
            if (!Auth::check()) {
                return $this->sendAuthenticatedError('Unauthorized', 'User is not authenticated.');
            }
            $limit = $request->input('limit', 10); // Default limit is 10 records
            $offset = $request->input('offset', 0); // Default offset is 0
            $customer = \App\Models\Customer::selectRaw("id,customer_name,customer_email,customer_phone_number")
                                ->skip($offset)
                                ->take($limit)
                                ->get();
                                
            if ($customer->isEmpty()) {
                return $this->sendResponseNoData($customer, 'No Data Available');
            } else {
                return $this->sendResponse($customer,'success');
            }
            return $this->sendResponse($customer,'success');
        } catch (Exception $e) {
            return $this->sendError('error', 'Internal Serve Error' . $e->getMessage(), 500);
        }
    }
}
