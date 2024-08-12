<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use Illuminate\Http\Request;
use App\Services\OrderService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CalculationController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTaxAndChargeCalculation(Request $request)
    {
        try {
            $totalAmount = 0;
            $totalSubtotal = 0;
            $totalTax = 0;
            $outletId = $request->outlet_id;
            $items = $request->items;
            $orderService = new OrderService();
            $tax = $orderService->getTax($outletId);
            foreach ($items as $item) {
                //fetch product price
                $subTotal = 0;
                $product = \App\Models\Product::find($item['product_id']);
                $subTotal = $product->product_price * $item['quantity'];
                $totalSubtotal += $subTotal;
                $totalTax += round($subTotal * ($tax[0] / 100), 2); // Round the result to 2 decimal places
                $totalAmount = round($totalSubtotal + $totalTax, 2); // Round the result to 2 decimal places                
            }
            $tax = \App\Models\Tax::where('outlet_id', $outletId)
            ->selectRaw('tax_name, tax_percentage, service_tax, packaging_charges')
            ->get();

            $data = [
                'totalTax'=>$totalTax,
                'totalSubtotal'=>$totalSubtotal,
                'totalAmount'=>$totalAmount,
                'tax_details'=>$tax,
            ];
            return $this->sendResponse($data,'Order calculation');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(),'Something Went Wrong');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
