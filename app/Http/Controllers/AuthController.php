<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request){
        $user = User::where('email', $request->email)->first();
        if (!$user) return response()->json(['message' => "User not found"], 404);
        if(!Hash::check($request->password, $user->password)) return response()->json(['message' => "Opps Wrong password"], 404);

        $token = $user->createToken('authToken')->plainTextToken;


        return response()->json([
            'message' => "Login successful",
             'data' => $user,
             'accessToken' => $token,
             'type' => 'Bearer'
        ], 200);
    }

    public function register(Request $request){
        /** Validator */
        $messages = [
            'name.required' => 'Please enter your name',
            'email.required' => 'We need to know your email address!',
            'email.email' => 'Please enter valid email address',
            'password.required' => 'Please enter password to register',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|max:50|min:8',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 404);
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Register Successfully'], 200);
    }

    public function user(Request $request){
        return $request->user();
    }

    public function logout(Request $request){
        // Xóa tất cả các token đăng nhập của user này
        auth()->user()->tokens()->delete();
        
        // Chỉ xóa mỗi token của user đang dùng này
        $request->user()->currentAccessToken()->delete();        
        return "Logout !";
    }
}
