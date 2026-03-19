<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Nette\Utils\Json;

class AuthLogin extends Controller
{
    public function signup(Request $request) {
        $valid = Validator::make($request->all(), [
            'username' => 'required|min:4|max:60|unique:users,username',
            'password' => 'required|min:5'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors(), 401);
        }

        do {
            $id = rand(1,636363636);
        } while (User::where('id', $id)->exists());

        User::create([
            'id' => $id,
            'username' => $request['username'],
            'password' => Hash::make($request['password']),
            'role_id' => '3',
            'last_login_at' => Carbon::now()
        ]);

        $user = User::where('username', $request['username'])->first();
        return response()->json([
            'status' => 'berhasil',
            'token' =>  $user->createToken('user_signup')->plainTextToken
        ],201);
    }

    //sigin / login user
    public function signin(Request $request) {
        $valid = Validator::make($request->all(), [
            'username' => 'required|min:4|max:60',
            'password' => 'required'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors(), 401);
        } elseif (!Auth::attempt($request->all())) {
            return response()->json([
                'status' => 'tidak valid',
                'message' => 'nama pengguna atau password salah'
            ], 401);
        }

        $user = User::where('username', $request['username'])->first();
        $user->tokens()->delete();
        $user->update([
            'last_login_at' => Carbon::now()
        ]);
        return response()->json([
            'status' => 'berhasil',
            'token' => $user->createToken('user_signin')->plainTextToken
        ]);
    }

    public function signout(Request $request) {

        $request->user()->currentAccessToken()->delete();
        return response()->json(['status' => 'berhasil'],200);
    }
}
