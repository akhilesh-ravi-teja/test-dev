<?php

namespace App\Http\Controllers\API;

use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services;
use App\Services\OrderService;
use Illuminate\Support\Facades\Storage;
use Validator;
use App\Validation\CreateOrderValidator;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;

class OrderProcessController extends BaseController
{
    public function orderProcess(OrderService $orderService,Request $request){
        $input = $request->all();
        $validator = CreateOrderValidator::validate($input);

        $validator->sometimes('customer_name', 'sometimes|string|max:255', function ($input) {
            return !empty($input->customer_name);
        });

        $validator->sometimes('outlet_id', 'sometimes|integer|exists:outlets,outlet_id', function ($input) {
            return !empty($input->outlet_id);
        });
        // Check if the 'customer_mobile' or 'customer_email' fields are present and validate them
        $validator->sometimes('customer_mobile', 'required|string|regex:/^\d{10}$/', function ($input) {
            return !empty($input->customer_mobile);
        });

        $validator->sometimes('customer_email', 'required|email', function ($input) {
            return !empty($input->customer_email);
        });

        $validator->sometimes('customer_id', 'sometimes|integer|exists:customers,id', function ($input) {
            return !empty($input->customer_id);
        });

        $validator->sometimes('order_id', 'sometimes|integer|exists:orders,id', function ($input) {
            return !empty($input->order_id);
        });

        $validator->sometimes('table_number', 'sometimes|integer|exists:tables,id', function ($input) {
            return !empty($input->table_number);
        });

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Validation passed, continue processing the order
        $data = $validator->validated();
        $orderResult = $orderService->createOrder($data);
        return $this->sendResponse($orderResult,'success');
    }

    public function getAllOrder(Request $request)
    {
        $value = $request->report;
        switch ($value) {
            case 'all':
                $orderResponse  = \App\Models\OrderItem::selectRaw('order_items.order_id,
                order_items.item_id,p.product_name,order_items.quantity,order_items.subtotal,o.customer_id,o.order_number,o.outlet_id,o.order_type')
                 ->join('orders AS o','o.id','=','order_items.order_id')
                 ->join('products AS p','p.id','=','order_items.item_id')
                 ->get();
                return $this->sendResponse($orderResponse, 'success');
            default:
                return $this->sendError('Invalid report value', ['report' => 'The report value is not valid']);
        }
    }
    
}
