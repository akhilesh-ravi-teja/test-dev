<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController as BaseController;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Validator;
use App\Http\Resources\CategoryResource;
use App\Models\Outlet;
use App\Models\User;
use Database\Factories\CategoryFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Nette\Utils\Validators;
use Carbon\Carbon;



class CategoryController extends BaseController
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
            $categories = Category::selectRaw('category.id AS category_id,category.category_name,category.description,category.status,o.outlet_id,o.user_id,category.created_at,category.updated_at,category.deleted_at')
            ->join('outlets AS o','o.outlet_id','=','category.outlet_id')
            ->when($outlet, function ($query) use ($outlet) {
                return $query->where('category.outlet_id', $outlet);
            })
            ->skip($offset)
            ->take($limit)
            ->get();
            
            if ($categories->isEmpty()) {
                return $this->sendResponseNoData($categories, 'No Data Available');
            } else {
                return $this->sendResponse($categories,'success');
            }
        } catch (\Exception $e) {
            return $this->sendAuthenticatedError('Internal Server Error', $e->getMessage(), 500); // 500 Internal Server Error
        }
       
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $categoryId = $input['category_id'] ?? '';
    
        $validator = Validator::make($input, [
            'category_name' => 'required',
            'description' => 'required',
            'outlet_id' => 'required',
        ]);
    
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
    
        if ($categoryId) {
            $updatedCategoryResponse = Category::where('id', $categoryId)
                ->update([
                    'category_name' => $input['category_name'],
                    'description' => $input['description'],
                ]);
            $response  = Category::find($categoryId);
            return $this->sendResponse(new CategoryResource($response), 'Category Updated successfully.');
        } else {
            $response = Category::updateOrCreate(
                [
                    'outlet_id' => $request->input('outlet_id'),
                    'category_name' => $request->input('category_name'),
                ],
                $input
            );
        }
        return $this->sendResponse(new CategoryResource($response), 'Category Created successfully.');
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
