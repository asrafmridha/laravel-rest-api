<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController as BaseController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use Auth;

class RegisterController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:6',
            'confirm_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        $password = Hash::make($request->password);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $password,
        ]);
        $success['token'] = $user->createToken('RestApi')->plainTextToken;
        $success['name'] =  $user->name;

        return $this->sendResponse($success, 'User register successfully');
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email'     => 'required|email|',
            'password'  => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendError("The password or email that you've entered is incorrect", $validator->errors());
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] = $user->createToken('RestApi')->plainTextToken;
            $success['name']  = $user->name;
            return $this->sendResponse($success, "User logged in Successfully");
        } else {
            
            $this->sendError('Unauthorized', ['error' => 'unauthorized']);
        }
    }
}
