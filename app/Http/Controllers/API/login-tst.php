use Illuminate\Support\Facades\Auth;

public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if ($validator->fails()) {
        return $this->sendError('Validation Error.', $validator->errors());
    }

    try {
        // Attempt to authenticate the user
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            // Check if the user has an existing valid token
            if ($user->tokens()->where('name', 'API Token')->first()) {
                // Valid token exists, return success response
                $user['token'] = $user->currentAccessToken()->plainTextToken;
                return $this->sendResponse($user, 'User login successfully.');
            } else {
                // No valid token exists, proceed with OTP verification and regeneration
                $OTPResponse = $this->otpVerification($request);
                return $this->sendResponse($OTPResponse, 'User login successfully with OTP.');
            }
        } else {
            return $this->sendError('Unauthorised.', ['error' => 'Unauthorised']);
        }
    } catch (\Exception $e) {
        return $this->sendError('Error occurred during user login.', $e->getMessage());
    }
}
