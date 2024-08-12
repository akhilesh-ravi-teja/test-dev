<?php 
namespace App\Http\Controllers\API\CustomClass;
use App\Http\Controllers\API\BaseController as BaseController;

use App\Models\OTP;
use App\Models\User;


class UserHelper extends BaseController{
    public function generateOTP($request){
        $email = $request;
        $users = User::where('email',$email)->pluck('id');
        dd($users);
        // $otp = OTP::Create(
        //     ['user_id' => $user->id],
        //     ['code' => rand(1000, 9000)]
        // );
    return $users;
        
    }

    public function sendOTP(){

    }

    public function generateNewToken(){
        
    }
}