<?php


namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
ini_set('memory_limit', '128M');

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];


        return response()->json($response, 200);
    }

    public function sendResponseNoData($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];


        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }

    public function sendAuthenticatedError($error, $errorMessages = [], $code = 401)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];


        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }

    // public function checkTokenExpiry($userId){
    //     $timeInterval = 60;
    //     $currentTime = Carbon::now();
    //     $tokenCreationTime = PersonalAccessToken::select('created_at')
    //                         ->where('tokenable_id', $userId)
    //                         ->orderBy('id', 'desc')
    //                         ->first();
    //     if ($tokenCreationTime) {
    //         $tokenCreationTime = Carbon::parse($tokenCreationTime->created_at);
    //         $elapsedMinutes = $tokenCreationTime->diffInMinutes($currentTime);
    //         if ($elapsedMinutes > $timeInterval) {
    //             return true;
    //         }else{
    //             return false;
    //         }
    //     }
    // }
    public function checkTokenExpiry($userId)
    {
        $timeInterval = 60;
        $currentTime = Carbon::now();
        $tokenCreationTime = PersonalAccessToken::select('created_at')
            ->where('tokenable_id', $userId)
            ->orderBy('id', 'desc')
            ->first();

        if ($tokenCreationTime) {
            $tokenCreationTime = Carbon::parse($tokenCreationTime->created_at)
                ->addHours(5)
                ->addMinutes(30);

            $elapsedMinutes = $tokenCreationTime->diffInMinutes($currentTime);

            if ($elapsedMinutes > $timeInterval) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function getUserId($email)
    {
        return User::where('email', $email)->pluck('id');
    }

    public function checkTokenIsValid($token)
    {
        $token = PersonalAccessToken::where('token', $token)->first();
        if ($token) {
            return true;
        } else {
            return false;
        }
    }

    public function checkLogin($email, $password)
    {
        try {
            if (Auth::attempt(['email' => $email, 'password' => $password])) {
                $user = Auth::user();
                $success['name'] = $user->name;
                $success['id'] = $user->id;
                $success['email'] = $user->email;

                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function generateQRCode($tableNumber){
        $string = $tableNumber;

        // add the string in the Google Chart API URL
        $googleChartApiUrl = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . $string . "&choe=UTF-8";

        // Fetch the QR code image from the Google Chart API
        $qrCodeImage = file_get_contents($googleChartApiUrl);

        // Store the QR code image on S3
        $filename = 'QRCODE/qrcode_' . time() . '.png';
        Storage::disk('s3')->put($filename, $qrCodeImage, 'public');

        // Display the generated QR code image URL
        $imageUrl = Storage::disk('s3')->url($filename);

        return $imageUrl;
    }

    public function sendMail($toEmail, $emailFrom, $data, $subject, $nameFrom) {
        try {
            \Mail::send('mail', $data, function ($message) use ($toEmail, $emailFrom, $subject, $nameFrom) {
                $message->from($emailFrom, $nameFrom);
                $message->to($toEmail)->subject($subject);
            });
    
            \App\Models\MailLog::create([
                'email' => $toEmail,
                'subject' => $subject,
                'mail_body' => $data['body1'],
                'status' => 'sent', // Add a status field to indicate success
            ]);
    
            return true;
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Error sending email: ' . $e->getMessage());
    
            // Log the failure in the mail log table
            \App\Models\MailLog::create([
                'email' => $toEmail,
                'subject' => $subject,
                'mail_body' => $data['body1'],
                'status' => 'failed', // Add a status field to indicate failure
                'error' => $e->getMessage(), // Log the error message
            ]);
    
            return false;
        }
    }
    
}
