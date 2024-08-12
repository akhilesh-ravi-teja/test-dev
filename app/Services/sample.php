<?php


function createOrder($data)
{
    try {
        DB::beginTransaction();

        // Initialize variables
        $customerId = null; // Placeholder for the customer ID
        $orderNumber = $this->generateOrderNumber(); // Generate order number
        $totalAmount = 0; // Initialize total amount

        // Create customer
        $customer = new CustomerService;
        $customerData = [
            'customer_name' => $data['customer_name'],
            'customer_mobile' => $data['customer_mobile'],
            'customer_email' => $data['customer_email'],
        ];
        $customerResult = $customer->createCustomerService($customerData);
        $customerId = $customerResult->id; // Get the customer ID

        // Calculate total amount and create order items
        $orderItems = [];
        foreach ($data['items'] as $item) {
            $product = Product::find($item['product_id']); // Fetch product details
            if ($product) {
                $subtotal = $product->product_price * $item['quantity']; // Calculate subtotal
                $totalAmount += $subtotal; // Add subtotal to total amount

                // Create order item
                $orderItems[] = [
                        'item_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'subtotal' => $subtotal,
                        'created_by' => Auth::user()->id, // Assuming you have authentication
                ];
            }
        }

        // Create order
        $order = \App\Models\Order::create([
            'customer_id' => $customerId,
            'order_number' => $orderNumber,
            'outlet_id' => $data['outlet_id'],
            // Add other fields as per your requirements
            'total_amount' => $totalAmount, // Set total amount
            'order_type'=>'dineIn',
            'payment_type'=>'cash',
            'order_status' => 'pending', // Set order status to pending
            'created_by' => Auth::user()->id, // Assuming you have authentication
        ]);

        // Create order items
        $orderItems = \App\Models\OrderItem::create($orderItems);

        DB::commit();

        return 'Transaction Successful';
    } catch (Exception $e) {
        DB::rollBack();
        return 'Internal Server Error: ' . $e->getMessage();
    }
}
