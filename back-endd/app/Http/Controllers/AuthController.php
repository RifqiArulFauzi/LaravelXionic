<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
//use Tymon\JWTAuth\Facades\JWTAuth;
use Firebase\JWT\JWT;


class AuthController extends Controller
{
    // public function login(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'nis' => 'required',
    //         'password' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 400);
    //     }
        
    //     $credentials = $request->only('nis', 'password');
    //     //dd(Auth::attempt($credentials));

    //     if (Auth::attempt($credentials)) {
    //         $user = Auth::user();
    //         $token = JWTAuth::fromUser($user);
    //         $jwtToken = JWTAuth::manager()->encode(
    //             JWTAuth::manager()->payload()
    //                 ->relatedTo($user->getKey()) // Pastikan nilai subjek sesuai dengan kebutuhan Anda
    //                 ->claims($customClaims)
    //                 ->getToken()
    //         );

    //         return response()->json(['token' => $jwtToken]);
    //     }

    //     return response()->json(['message' => 'Invalid credentials'], 401);
    // }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nis' => 'required',
            'password' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        
        $credentials = $request->only('nis', 'password');

        //dd(Auth::attempt($credentials));
        if (Auth::attempt($credentials)) {

            $user = Auth::user();
            //dd($user);
            $payload = [
                'id' => $user->IdUser,
                'nama' => $user->nama,
                'role' => $user->role,
                'iat' => now()->timestamp,
                'exp' => now()->timestamp + 7200,
            ];
    
            $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');


            return response()->json([
                "data" => [
                    'id' => Auth::user()->IdUser,
                    'msg' => "berhasil login",
                    'nama' => Auth::user()->nama,
                    'role' => Auth::user()->role,
                ],
                "token" => "{$token}"
            ],200);
        }

        //return messageError($validator->messages()->toArray());

        return response()->json(['error' => 'Invalid nis or password'], 401);
    }

    public function register(Request $request){

        $validator = validator::make($request->all(),[
            'nis' => 'required|min:5|max:5|unique:user',
            'nama' => 'required',
            'password' => 'required|min:8',
            'confirmation_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            return messageError($validator->messages()->toArray());
        }
        
        $user = $validator->validated();

        User::create($user);

        $payload = [
            'nama' => $user['nama'],
            'role' => 'User',
            'iat' => now()->timestamp,
            'exp' => now()->timestamp + 7200,
        ];

        $token = JWT::encode($payload,env('JWT_SECRET'), 'HS256');


        return response()->json([
            "data" => [
                //'id' => $user['IdUser'],
                'msg' => "berhasil login",
                'nama' => $user['nama'],
                'nis' => $user['nis'],
                'role' => 'user',
            ],
            "token" => "Bearer {$token}"
        ], 200);
    }


}

