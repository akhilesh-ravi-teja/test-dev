<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Validation\CreateCustomerValidator;
use App\Services\CustomerService;
use App\Enums;
use App\Enums\OrderStatus;
use App\Enums\PaymentType;
use App\Models\Outlet;
use App\Models\Order;

class OrderService
{

    public function createOrder($data)
    {
        try {
            DB::beginTransaction();
            //create customer
            $customer = new CustomerService;
            $orderItems =  [];
            $totalAmount = 0;
            $totalSubtotal = 0;
            $totalTax = 0;
            $tax = $this->getTax($data['outlet_id']);
            // Create order if request does not have customer_id
            if (!isset($data['customer_id'])) {
                $customerData = [
                    'customer_name' => $data['customer_name'],
                    'customer_mobile' => $data['customer_mobile'],
                    'customer_email' => $data['customer_email'],
                ];
                $customerResult = $customer->createCustomerService($customerData);
                $order = \App\Models\Order::create([
                    'customer_id' => $customerResult->id,
                    'order_number' => $this->generateOrderNumber($data['outlet_id']),
                    'outlet_id' => $data['outlet_id'],
                    'order_type' => $data['order_type'],
                    'order_status' => $data['order_status'],
                    'payment_type' => $data['payment_type'] ?? '',
                    'created_by' => Auth::user()->id,
                ]);
            }

            $orderItemsExist = \App\Models\OrderItem::where('order_id', $order->id ?? $data['order_id'])
                ->exists();

            if ($orderItemsExist) {
                \App\Models\OrderItem::where('order_id', $order->id ?? $data['order_id'])
                    ->delete();
            }

            foreach ($data['items'] as $item) {
                //fetch product price
                $subTotal = 0;
                $product = \App\Models\Product::find($item['product_id']);
                $subTotal = $product->product_price * $item['quantity'];
                $totalSubtotal += $subTotal;
                $totalTax += round($subTotal * ($tax[0] / 100), 2); // Round the result to 2 decimal places
                $totalAmount = round($totalSubtotal + $totalTax, 2); // Round the result to 2 decimal places                

                $orderItemsResult = \App\Models\OrderItem::updateOrCreate(
                    [
                        'order_id' => $order->id ?? $data['order_id'],
                        'item_id' => $product->id,
                    ],
                    [
                        'quantity' => $item['quantity'],
                        'subtotal' => $subTotal,
                        'created_by' => Auth::user()->id,
                    ]
                );
            }
            
            $orderUpdate = order::find($order->id ?? $data['order_id']);
            $orderDetails = $orderUpdate->update([
                'total_tax' => $totalTax,
                'total_subtotal' => $totalSubtotal,
                'total_amount' => $totalAmount,
                'order_status' => $data['order_status'],
                'payment_type' => $data['payment_type'],
                'created_by' => Auth::user()->id,
            ]);

            //invoice generatation            
            if (
                $data['order_status'] === OrderStatus::COMPLETED->value &&
                ($data['payment_type'] === PaymentType::CASH->value || $data['payment_type'] === PaymentType::ONLINE->value)
            ) {
                $invoice = $this->createInvoice($data['outlet_id'], $order->id ?? $data['order_id'], $order->order_status??$data['order_status'], $data['payment_type'], $totalAmount);
            }

            // $orderResponse = 'Transaction Successful';
            $orderResponse  = \App\Models\OrderItem::selectRaw('order_items.order_id,
           order_items.item_id,p.product_name,order_items.quantity,order_items.subtotal,o.customer_id,o.order_number,o.outlet_id,o.order_type,o.order_status')
                ->join('orders AS o', 'o.id', '=', 'order_items.order_id')
                ->join('products AS p', 'p.id', '=', 'order_items.item_id')
                ->where('o.id', $order->id ?? $data['order_id'])
                ->get();
            DB::commit();

            $orderItems = $orderResponse->map(function ($item) {
                return [
                    'order_id' => $item->order_id,
                    'item_id' => $item->item_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                    'customer_id' => $item->customer_id,
                    'order_number' => $item->order_number,
                    'outlet_id' => $item->outlet_id,
                ];
            });

            $order = [
                'order_id' => $order->id ?? $data['order_id'],
                'total_tax' => round($totalTax, 2),
                'total_subtotal' => round($totalSubtotal, 2),
                'total_amount' => round($totalAmount, 2),
                'order_status' => $orderResponse[0]->order_status,
            ];

            return [
                'order' => $order,
                'order_items' => $orderResponse,
                'invoice' => $invoice ?? null,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return 'Internal Server Error: ' . $e->getMessage() . ' at line ' . $e->getLine();
        }
    }

    public function createInvoice($outletId, $orderId, $orderStatus, $paymentType, $totalAmount)
    {
        try {
            return \App\Models\Invoice::updateOrCreate(

                [
                    'order_id' => $orderId,
                ],
                [
                    'outlet_id' => $outletId,
                    'order_id' => $orderId,
                    'invoice_number' => $this->generationInvoiceNumber($outletId),
                    'total_amount' => $totalAmount,
                    'invoice_status' => \App\Enums\InvoiceStatus::PAID,
                    'created_by' => Auth::user()->id,
                ]
            );
        } catch (Exception $e) {
            return 'Internal Server Error' . $e->getMessage();
        }
    }

    public function createPayment()
    {
    }

    public function generationInvoiceNumber($outletId)
    {
        $latestInvoice = \App\Models\Invoice::where('outlet_id', $outletId)
            ->orderByDesc('id')
            ->select('invoice_number')
            ->first();

        if ($latestInvoice) {
            $currentInvoiceNumber = (int) $latestInvoice->invoice_number;
            $nextInvoiceNumber = $currentInvoiceNumber + 1;
        } else {
            $nextInvoiceNumber = 1001;
        }

        return $nextInvoiceNumber;
    }

    public function generateOrderNumber($outletId)
    {
        $latestOrder = \App\Models\Order::where('outlet_id', $outletId)
            ->orderByDesc('id')
            ->select('order_number')
            ->first();

        if ($latestOrder) {
            $currentOrderNumber = (int) $latestOrder->order_number;
            $nextOrderNumber = $currentOrderNumber + 1;
        } else {
            $nextOrderNumber = 1001;
        }

        return $nextOrderNumber;
    }

    public function getTax($outletId)
    {
        $result = \App\Models\Tax::selectRaw('SUM(CASE WHEN tax_name = ? OR tax_name = ? THEN tax_percentage ELSE 0 END) AS total_tax_percentage', ['CGST', 'SGST'])
            ->where('outlet_id', $outletId)
            ->pluck('total_tax_percentage');
        return $result;
    }
}
