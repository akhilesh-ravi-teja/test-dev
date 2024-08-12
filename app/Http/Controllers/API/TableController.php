<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\TableResource;
use App\Models\Table;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Storage;


class TableController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        try {
            $user = Auth::user();
            $limit = $request->input('limit', 10); // Default limit is 10 records
            $offset = $request->input('offset', 0); // Default offset is 0
            $outlet = $request->has('outlet_id') ? $request->outlet_id : '';
            $data = Table::join('outlets AS o', 'o.outlet_id', '=', 'tables.outlet_id')
                                ->leftJoin('qr_codes AS q','q.table_id','=','tables.id')
                                ->where('o.user_id', $user->id)
                                ->skip($offset)
                                ->take($limit)
                                ->when($outlet, function($query) use ($outlet){
                                    return $query->where('tables.outlet_id', $outlet);
                                })
                                ->selectRaw('o.outlet_id,tables.id,tables.table_number,q.id as qr_id,q.qr_code_path,tables.table_status,tables.created_at,tables.updated_at')
                                ->get();
        
            if ($data->isEmpty()) {
                return $this->sendResponseNoData($data, 'No Data Available');
            } else {
                return $this->sendResponse($data,'success.');
            }
        } catch (Exception $e) {
            return $this->sendError('error', 'An error occurred while fetching data'.$e->getMessage(), 500);
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
    try {
        $user = Auth::user();
    
        if (!$user) {
            return $this->sendAuthenticatedError('Unauthorized', 'User is not authenticated.');
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'table_count' => 'required|integer|min:1',
            'outlet_id'=>'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400); // 400 Bad Request
        }

        $tableCount = $request->input('table_count');
        $outletId = $request->input('outlet_id');

        for ($i = 1; $i <= $tableCount; $i++) {
            $table = Table::updateOrCreate(['outlet_id' => $outletId, 'table_number' => $i]);
        
            // Check if ModelsQrCode entry already exists for the current table_id
            $existingQrCode = \App\Models\QrCode::where('table_id', $table->id)->first();
        
            // If ModelsQrCode entry doesn't exist, create a new one
            if (!$existingQrCode) {
                // Generate QR code and store in S3
                $qrCodePath = $this->generateQRCode($i);
        
                \App\Models\QrCode::create([
                    'qr_code_path' => $qrCodePath,
                    'table_id' => $table->id,
                ]);
            }
        }


        // Fetch all tables after creation
        $allTables = Table::join('outlets AS o', 'o.outlet_id', '=', 'tables.outlet_id')
                                ->leftJoin('qr_codes AS q','q.table_id','=','tables.id')
                                ->where('o.user_id', $user->id)
                                ->where('tables.outlet_id',$outletId)
                                ->selectRaw('o.outlet_id,tables.id,tables.table_number,q.id as qr_id,q.qr_code_path,tables.table_status,tables.created_at,tables.updated_at')
                                ->get();
    
        // Return a success response with table details
        return $this->sendResponse($allTables ,'success'); // 201 Created
    } catch (Exception $e) {
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
