<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    
    public function index()
    {
        return ResponseHelper::make(200, 'Welcome to login page! Please log in!', []);
    }

    public static function login(Request $request) {
        $data = $request->only(['user_name', 'password']);

        $validator = Validator::make($data, [
            'user_name' => ['required'],
            'password' => ['required']
        ]);

        if ($validator->fails()) {
            return ResponseHelper::make(422, 'You have the following errors:', $validator->getMessageBag());
        }

        if(!Auth::attempt($data)) {
            return ResponseHelper::make(401, 'You have inputted incorrect user credentials.', $data);
        }

        $scopes = User::findOrFail(auth()->user()->id)->pluck('user_role')->toArray();

        $message = '';
        $status = 200;
        if(auth()->user()->is_verified) {
            $accessToken = auth()->user()->createToken('_token', $scopes)->accessToken;
            $message = 'Successfully logged in!';
            $user = [
                'id' => auth()->user()->id,
                'name' => auth()->user()->id,
                'email' => auth()->user()->email,
                'user_role' => auth()->user()->user_role,
                '_token' => $accessToken
                
            ];
        } else {
            $message = 'You need to verify your account first sent to your email.';
            $status = 401;
            $user = [
                '_token' => ''
            ];
        }

        return ResponseHelper::make($status, $message, $user);

    }

    public function loginVerification(Request $request) {

        $data = $request->only(['user_name', 'password', 'pin']);

        $messages = [
            'min' => 'The inputted pin field is incorrect.',
            'max' => 'The inputted pin field is incorrect.'
        ];
        $validator = Validator::make($data, [
            'user_name' => ['required'],
            'password' => ['required'],
            'pin' => ['required', 'min:6', 'max:6'],
        ], $messages);

        if ($validator->fails()) {
            return ResponseHelper::make(422, 'You have the following errors:', $validator->getMessageBag());
        }

        if(!Auth::attempt($data)) {
            return ResponseHelper::make(401, 'You have inputted incorrect user credentials.', $data);
        }

        $scopes = User::findOrFail(auth()->user()->id)->pluck('user_role')->toArray();

        $message = '';
        $status = 200;
        if(auth()->user()->is_verified == 0 && auth()->user()->pin == $data['pin']) {
            $user = User::findOrFail(auth()->user()->id)->update([
                'registered_at' => now(),
                'is_verified' => 1,
                'pin' => null,
            ]);
            $accessToken = auth()->user()->createToken('_token', $scopes)->accessToken;
            $message = 'Successfully registered and logged in!';
            $user = [
                'id' => auth()->user()->id,
                'name' => auth()->user()->id,
                'email' => auth()->user()->email,
                'user_role' => auth()->user()->user_role,
                '_token' => $accessToken
                
            ];
        }

        return ResponseHelper::make($status, $message, $user);

    }

    public function logout (Request $request) {
        $token = $request->user()->token();
        $token->revoke();

        return ResponseHelper::make(200, 'You have been successfully logged out.', $request->all());
    }
}
