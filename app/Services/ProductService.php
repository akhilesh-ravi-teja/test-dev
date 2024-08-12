<?php 
namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class ProductService
{
    
    static public function uploadToS3($base64Data, $directory)
    {
        // Decode the base64-encoded data
        $fileData = base64_decode($base64Data);

        // Generate a unique file name
        $fileName = uniqid() . '.jpg'; // You can use a different extension based on the image type

        // Store the file in the "s3" disk under the specified directory with the unique file name
        Storage::disk('s3')->put($directory . '/' . $fileName, $fileData);
        
        // Make the file public
        Storage::disk('s3')->setVisibility($directory . '/' . $fileName, 'public');


        // Generate a temporary public URL for the uploaded file
        return Storage::disk('s3')->url($directory . '/' . $fileName);
    }

    static public function isBase64Encoded($data)
    {
        // Remove data URI scheme if present
        $data = preg_replace('#^data:image/\w+;base64,#i', '', $data);

        // Decode the data
        $decodedData = base64_decode($data, true);

        // Check if the decoding was successful and the result is not empty
        return ($decodedData !== false && $decodedData !== '');
    }

}
