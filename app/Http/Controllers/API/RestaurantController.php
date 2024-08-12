<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Product;
use Validator;
use App\Http\Resources\ProductResource;
use App\Http\Resources\RestaurantResource;
use App\Models\Outlet;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use App\Models\Restaurant;
use App\Models\User;
use GuzzleHttp\Promise\Create;
use Illuminate\Support\Facades\Auth;
ini_set('memory_limit', '128M');

class RestaurantController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index(Request $request)
    // {
    //     try {
    //         $data = Outlet::where('user_id', Auth::user()->id)->limit(3)->get();

    //         if ($data->isEmpty()) {
    //             return $this->sendResponseNoData($data, 'No Data Available');
    //         } else {
    //             return $data;
    //         }
    //     } catch (\Exception $e) {
    //         return $this->sendError('error', 'An error occurred while fetching data', 500);
    //     }
    // }
    public function index(Request $request){
    try {
        $user = Auth::user();
        $limit = $request->input('limit', 10); // Default limit is 10 records
        $offset = $request->input('offset', 0); // Default offset is 0

        $data = Outlet::where('user_id', $user->id)
            ->skip($offset)
            ->take($limit)
            ->get();

        if ($data->isEmpty()) {
            return $this->sendResponseNoData($data, 'No Data Available');
        } else {
            return $this->sendResponse($data,'success.');
        }
    } catch (\Exception $e) {
        return $this->sendError('error', 'An error occurred while fetching data'.$e->getMessage(), 500);
    }
}
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Check if the user is authenticated
            if (!Auth::check()) {
                return $this->sendAuthenticatedError('Unauthorized', 'User is not authenticated.');
            }
           
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'outlet_name' => 'required',
                'location' => 'required',
                'address' => 'required',
                'latlong' => 'required',
            ]);
    
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 400); // 400 Bad Request
            }

            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('products', 's3'); // Use 's3' disk
                $finalUrl = Storage::disk('s3')->url($logoPath);
            }
    
            $conditions = [
                'user_id' => Auth::user()->id,
                'outlet_id' => $request->input('outlet_id'),
            ];

            $data = [
                'outlet_name'=>$request->input('outlet_name'),
                'location' => $request->input('location'),
                'address' => $request->input('address'),
                'latlong' => $request->input('latlong'),
                'logo'=>$finalUrl ?? '',
            ];

            $restaurant = Outlet::updateOrCreate($conditions, $data);
            // Return a success response with restaurant details
            return $this->sendResponse($restaurant,'Restaurant created successfully.', 201); // 201 Created
        } catch (\Exception $e) {
            return $this->sendAuthenticatedError('Internal Server Error', $e->getMessage(), 500); // 500 Internal Server Error
        }
    }
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
    }
}
