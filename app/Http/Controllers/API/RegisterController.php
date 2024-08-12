<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\OTP;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\PaymentPlan;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\API\CustomClass\UserHelper;
use App\Http\Resources\UserResource;
use Illuminate\Validation\Rules\Exists;
use PhpParser\Node\Expr\FuncCall;
use Carbon\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\Sanctum;
use Laravel\Sanctum\Tokens\Token;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
ini_set('memory_limit', '128M');

class RegisterController extends BaseController
{
    /**
     * Register api
     * 
     * @return \Illuminate\Http\Response
     * Register
     * SendOtp
     * GenerateToken
     * Login
     */

    public function register(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|numeric|digits:10',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        try {
            DB::beginTransaction();

            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $input['role'] = 'admin';
            $input['status'] = 'pending';
            // Handle image upload
            try {
                if ($request->hasFile('profile_pic')) {
                    $file = $request->file('profile_pic');
                    // Generate a unique filename
                    $fileNameRaw = 'profile_pic_' . uniqid();
                    // Upload the file to S3
                    // $profilePicPath = Storage::disk('s3')->putFileAs('products', $file, $filename, 'public');
                    $profilePicPath = Storage::disk('s3')->put($fileNameRaw,$file);
                    // Get the S3 URL
                    $finalUrl = Storage::disk('s3')->url($profilePicPath);
                
                    $input['profile_pic'] = $finalUrl;
                }
        
            } catch (\Exception $e) {
                \Log::error('Error uploading file to S3: ' . $e->getMessage());
                throw $e;
            }
            $user = User::create($input);
            $user->role = $input['role'];
            $user->status = $input['status'];
            $user->save();

            // Generate and save OTP
            $otp = OTP::updateOrCreate(
                ['user_id' => $user->id],
                ['code' => rand(1000, 9000)]
            );

            DB::commit();
            $response = $user;
            $response->otp = $otp->code;

            //send OTP on mail
            $emailTemplateData = \App\Models\EmailTemplate::where('mail_type', 'OTP')->first();
            $body = str_replace('{otp}',  $response->otp, $emailTemplateData->mail_body);
            $data = array(
            'body1' => $body,
            );
            $toEmail = $request->email;
            $emailFrom = $emailTemplateData->mail_from;
            $subject = $emailTemplateData->subject;
            $nameFrom = $emailTemplateData->name_from;
            $this->sendMail($toEmail,$emailFrom,$data,$subject,$nameFrom);
            return $this->sendResponse($response, 'You will shortly receive an OTP.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error occurred during user registration.', $e->getMessage());
        }
    }

    public function otpVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'otp' => 'required|numeric|digits:4',
            'device_details' => 'required',
        ], [
            'otp.digits' => 'The OTP must be 4 characters long.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->sendError('Validation Error.', ['email' => 'User not found.']);
            }

            $otpDetails = OTP::where('user_id', $user->id)->first();

            if ($otpDetails && $otpDetails->code == $request->otp) {
                DB::beginTransaction();

                try {
                    $user->device_details = $request->device_details;
                    $token = $user->createToken('Myapp')->plainTextToken;
        
                    // $PAT = PersonalAccessToken::where('tokenable_id', $user->id)->first();
                    // $token = $PAT->token;
                    $response = [
                        'user' => $user,
                        'access_token' => $token,
                    ];
                    // $user->status = 'confirmed';
                    // $user->save();
                    $otpDetails->delete(); // Delete OTP record
                    DB::commit();
                    return $this->sendResponse($response, 'User Registered Successfully.');
                } catch (\Exception $e) {
                    DB::rollback();
                    return $this->sendError('Error occurred during user registration.', $e->getMessage());
                }
            } else {
                return $this->sendAuthenticatedError('message', 'Not Authenticated!');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error occurred during user verification.', $e->getMessage());
        }
    }


    public function oTpRegenerate(Request $request)
    {
        $userDetails = User::where('email', '=', $request->email)->get();

        if (!$userDetails->isEmpty()) {
            $code = rand(1000, 9000);
            $input = [
                'user_id' => $userDetails[0]->id,
                'code' => $code
            ];

            $response = OTP::updateOrCreate(['user_id' => $input['user_id']], $input);
            // $response = $userDetails;
            return $this->sendResponse($response, 'OTP generated Successfully');
        } else {
            return $this->sendAuthenticatedError("Email does not exist");
        }
    }


    public function logout(Request $request)
    {

        if ($request->email) {
            $user = User::where('email', $request->email)->first();
            if ($user->tokenCan('server:update')) {
                return $this->sendResponse('message', 'You have successfully logged out.');
            }
            $user->tokens()->where('tokenable_id', $user->id)->delete();

            return $this->sendResponse('message', 'You have successfully logged out.');
        } else {
            return $this->sendError('Unauthenticated.', ['error' => 'You are not authenticated.']);
        }
    }

    public function deleteuser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $user = User::findOrFail($request->user_id);

            DB::beginTransaction();

            // Delete the associated restaurant and its tables
            $restaurant = Restaurant::where('user_id', $user->id)->first();
            if ($restaurant) {
                $restaurant->where('restaurant_id', $restaurant->restaurant_id)->delete();
            }

            // Delete the user
            $user->delete();

            DB::commit();

            return $this->sendResponse([], 'User deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error occurred while deleting user.', $e->getMessage());
        }
    }

    /**
     * if token is null or empty then authenticate and generate token
     * if token is expired then authenticate and generate token
     * 
     */



    /**
     * - If signing first time in new device (Token will be Empty)
     * - Send OTP 
     * - After OTP Verifications send New Token with user details 
     * - If signing with invalid token
     * - Send OTP 
     * - After OTP Verifications send New Token with user details
     * - If signing with Valid token
     * - Show all user details
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Case1 - If signing first time in new device
        $user = User::where('email', $request->email)->first();
        if ($user->status == 'pending') {
            $user->status = 'confirmed';
            $user->save();
            $otp = $this->oTpRegenerate($request);
            $otp = json_decode($otp->getContent());
            return response()->json([
                'data' => $otp,
                'message' => 'Sign in First Time',
            ]);
        }

        // Case2 - If signing with valid credentials
        $loginCheck =  $this->checkLogin($request->email, $request->password);
        if ($loginCheck) {
            $user = Auth::user();
            $success['name'] = $user->name;
            $success['id'] = $user->id;
            $success['email'] = $user->email;
            $userDetails = User::findOrFail($user->id);
            $response = $userDetails;
            $userDetails['token'] = $user->createToken('Myapp')->plainTextToken;
            return $this->sendResponse(new UserResource($response), 'User login successful.');
        } else {
            return $this->sendError('Unauthorised.', ['error' => 'Unauthorised']);
        }
    }


    public function forgotPassword(Request $request)
    {
        $token = Auth::user();
        dd($token);
    }

    public function resetPassword(Request $request)
    {
    }
}
