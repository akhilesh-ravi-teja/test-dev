<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Product;
use Validator;
use App\Http\Resources\ProductResource;
use App\Models\Outlet;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Exception;
use Illuminate\Support\Facades\App;
use App\Enums\ProductType;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use App\Validation\CreateProductValidator;

ini_set('memory_limit', '128M');

class ProductController extends BaseController
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            if (!Auth::check()) {
                return $this->sendAuthenticatedError('Unauthorized', 'User is not authenticated.');
            }
            $limit = $request->input('limit', 10); // Default limit is 10 records
            $offset = $request->input('offset', 0); // Default offset is 0
            $outlet = $request->has('outlet_id') ? $request->outlet_id : '';
            $products = Product::selectRaw("products.outlet_id,products.product_type,c.`id` AS category_id, c.`category_name`,c.`description`,
                                            products.`id` AS product_id,products.`product_name`,
                                            products.`product_description`,
                                            products.`product_image`,products.`product_price`,
                                            products.`updated_at`,products.`created_at`,products.`deleted_at`")
                ->join('category AS c', 'c.id', '=', 'products.category_id')
                ->when($outlet, function ($query) use ($outlet) {
                    return $query->where('products.outlet_id', $outlet);
                })
                ->skip($offset)
                ->take($limit)
                ->get();

            if ($products->isEmpty()) {
                return $this->sendResponseNoData($products, 'No Data Available');
            } else {
                return $this->sendResponse($products, 'success');
            }
        } catch (\Exception $e) {
            return $this->sendError('error', 'An error occurred while fetching data', 500);
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
            $input = $request->all();
            if ($request->has('product_id')) {
                $validator = CreateProductValidator::validateForProductUpdate($input);

                $validator->sometimes('product_name', 'sometimes|string|max:255', function ($input) {
                    return !empty($input->product_name);
                });

                $validator->sometimes('product_type', ['sometimes', Rule::in(['veg', 'nonveg', 'vegan']),], function ($input) {
                    return !empty($input->product_type);
                });

                $validator->sometimes('product_description', 'sometimes|string|max:255', function ($input) {
                    return !empty($input->product_description);
                });

                $validator->sometimes('product_image', 'sometimes', function ($input) {
                    return !empty($input->product_image);
                });

                if ($validator->fails()) {
                    return $this->sendError('Validation Error.', $validator->errors());
                }

                $product = \App\Models\Product::find($request->product_id);
                if (isset($input['product_image'])) {
                    $isBase64 = \App\Services\ProductService::isBase64Encoded($input['product_image']);
                    if ($isBase64) {
                        $url = \App\Services\ProductService::uploadToS3($input['product_image'], 'product_image');
                        $product->product_image = $url;

                    }
                }

                  // Update product attributes if they are present in the input
                foreach (['category_id', 'product_type', 'product_name', 'product_description', 'product_price'] as $attribute) {
                    if (isset($input[$attribute])) {
                        $product->$attribute = $input[$attribute];
                    }
                }
                if ($product->save()) {
                    return $this->sendResponse($product,'Product updated successfully.');
                } else {
                    return $this->sendError('Failed to update product.');
                }
        
            } 
            else {
                $validator = CreateProductValidator::validateNewProduct($input);

                $validator->sometimes('product_image', 'sometimes', function ($input) {
                    return !empty($input->product_image);
                });

                if ($validator->fails()) {
                    return $this->sendError('Validation Error.', $validator->errors());
                }
                $productId = [];
                foreach ($input['product_data'] as $data) {
                    if (isset($data['product_image'])) {
                        $isBase64 = \App\Services\ProductService::isBase64Encoded($data['product_image']);
                        if ($isBase64) {
                            $url = \App\Services\ProductService::uploadToS3($data['product_image'], 'product_image');
                        }
                    }
                            $product = \App\Models\Product::create([
                                'outlet_id' => $input['outlet_id'],
                                'category_id' => $input['category_id'],
                                'product_type' => $data['product_type'],
                                'product_name' => $data['product_name'],
                                'product_description' => $data['product_description'] ?? '',
                                'product_price' => $data['product_price'],
                                'product_image' => $url ?? null,
                            ]);

                            $productId[] = $product['id'];
                
                }
            }

            $productsDetails = Product::selectRaw("products.outlet_id,c.`id` AS category_id, c.`category_name`,c.`description`,
                                            products.`id` AS product_id,products.`product_name`,products.`product_type`,
                                            products.`product_description`,
                                            products.`product_image`,products.`product_price`,
                                            products.`updated_at`,products.`created_at`,products.`deleted_at`")
                ->join('category AS c', 'c.id', '=', 'products.category_id')
                ->whereIn('products.id', $productId ?? $request->product_id)
                ->get();

            return $this->sendResponse($productsDetails, 'Product created successfully.');
        } catch (\Exception $e) {
            return $this->sendError('error', 'An error Occured' . $e->getMessage(), 500);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return $this->sendResponse([], 'Product deleted successfully.');
    }
}
