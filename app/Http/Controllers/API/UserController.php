<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Mail\Invitation;
use App\Mail\Verification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Lcobucci\JWT\Signer\Rsa;

class UserController extends Controller
{
    public function show() 
    {
        $user = User::findOrFail(auth()->user()->id);

        return ResponseHelper::make(200, 'Welcome to your profile!', $user);
    }

    public function update(Request $request, User $user) {
        $data = $request->all();
        if (auth()->user()->id != $user->id) {
            return ResponseHelper::make(401, 'You are not allowed to update others profile', []);
        }

        $messages = [
            'dimensions' => 'The avatar has invalid image dimensions. The maximum width and height is 256px'
        ];

        $validator = Validator::make($data, [
            'user_name' => ['required', Rule::unique('users', 'user_name')->ignore($user), 'min:4', 'max:20'],
            'avatar' => ['nullable', 'mimes:png,jpg', Rule::dimensions()->maxWidth(256)->maxHeight(256),],
            'email' => ['required', 'email', Rule::unique('users', 'user_name')->ignore($user)]
        ], $messages);
      
        if($validator->fails()){
            return ResponseHelper::make(422, 'You have the following errors:', $validator->getMessageBag());
        } 

          
        $avatar = $request->file('avatar');
        if($avatar){    
            $image_uploaded_path = $avatar->store('users', 'public');
            $data['avatar'] = $image_uploaded_path;
            $data['image_url'] = Storage::disk('public')->url($image_uploaded_path);
            $data['image_mime'] = $avatar->getClientMimeType();
            
        }

        $user->update($data);

        return ResponseHelper::make(200, 'Profile updated successfully.', $data);

    }

    public function getRegister()
    {
        return ResponseHelper::make(200, 'Welcome to our registration page! Please fill the following fields.', []);
    }


    public function register(Request $request) {
        $data = $request->all();
        $decrypted = Crypt::decrypt($data['user']);
        $random_digit = random_int(100000, 999999);


        $user = User::where('email', $decrypted)->first();
        if($user){
 
            
            $validator = Validator::make($data,[
                'user_name' => ['required', 'min:4', 'max:20'],
                'password' => ['required', 'min:4', 'max:20']
            ]);

            if($validator->fails()) {
                return ResponseHelper::make(422, 'You have the following errors:', $validator->getMessageBag());
            }

            $data['password'] = Hash::make($data['password']);
            $data['pin'] = $random_digit;
            
     
            User::where('email', $decrypted)->update([
                'user_name'  => $data['user_name'],
                'password'  => $data['password'],
                'pin'  => $data['pin'],
            ]);



            if($user) {
                Mail::to($decrypted)->send(new Verification($user));  
                return ResponseHelper::make(200, 'Successfully registered account please check email to verify your account.', $data);
            } else {
                return ResponseHelper::make(422, 'Oops something went wrong registering your account.', []);
            }



        } else {

            return ResponseHelper::make(422, 'Opps something went wrong registering your account. Please try to click and register from your email or the email you are trying to registering is not registered in our database.', []);
        }
    }


    public function sendInvitation(Request $request) {
        $data = $request->only(['email']);
        $validator = Validator::make($data, [
            'email' => ['required', 'email']
        ]);
        
        if($validator->fails()){
            return ResponseHelper::make(422, 'You have the following errors', $validator->getMessageBag());
        }

        if(User::where([['email', $data['email']]])->count() > 0){
            return ResponseHelper::make(422, 'User already registed.', []);
        }

        Mail::to($data['email'])->send(new Invitation(auth()->user(), $data['email']));  
        
        $data['user_role'] = 'user';
        User::create($data);
        return ResponseHelper::make(200, 'Email Invitation has been sent', []); 
    }
}
