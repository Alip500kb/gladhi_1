<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function admins() {
        if (!Gate::allows('admins')) {
            return response()->json(['status' => 'dilarang', 'message' => 'anda bukan administrator'], 403);

        }

        $list = User::where('role_id', '1')->get()->map(fn ($list) => [
            'nama pengguna' => $list['username'],
            'last_login_at' => $list['last_login_at'],
            'created_at' => $list->created_at,
            'updated_at' => $list->updated_at
        ]);

        return new UserResource($list, "berhasil", 'berhasil');
    }

    public function users() {
        if (!Gate::allows('admins')) {
            return response()->json(['status' => 'dilarang', 'message' => 'anda bukan administrator'], 403);
        }

        $pengguna = User::where('role_id', '3')->get()->map(fn ($pengguna) => [
            'nama pengguna' => $pengguna->username,
            'last_login_at' => $pengguna->last_login_at,
            'created_at' => $pengguna->created_at,
            'updated_at' => $pengguna->updated_at
        ]);

        return new UserResource($pengguna, 'ahy', 'berhasil');
    }

    public function  userdetail($username) {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['status' => 'gagal', 'message' => 'nama pengguna tidak ditemukan'], 400);
        }

        return response()->json([
            'nama pengguna' => $user['username'],
            'last_login_at' => $user['last_login_at'],
            'created_at' => $user['created_at'],
            'updated_at' => $user['updated_at']
        ]);
    }

    public function newuser(Request $request) {

        $valid = Validator::make($request->all(), [
            'username' => 'required|min:4|max:60',
            'password' => 'required|min:5'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors(), 401);
        } elseif (User::where('username', $request['username'])->exists()) {
            return response()->json([
                'status' => 'tidak valid',
                'message' => 'nama pengguna telah digunakan'
            ],401);
        }

        do {
            $id = rand(1,66363636);
        } while (User::where('id', $id)->exists());

        $user = User::create([
            'id' => $id,
            'username' => $request['username'],
            'password' => $request['password'],
            'role_id' => '3',
            'last_login_at' => Carbon::now()
        ]);

        return response()->json([
            'status' => 'berhasil',
            'token' => $user->createToken('user_insert')->plainTextToken
        ],201);
    }

    public function updateuser(Request $request,$id) {
        $valid = Validator::make($request->all(), [
            'username' => 'sometimes|min:4|max:60',
            'password' => 'sometimes|min:5'
        ]);
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'tidak valid',
                'message' => 'pengguna tidak ditemukan'
            ],401);
        } elseif (User::where('username', $request['username'])->exists()) {
            return response()->json([
                'status' => 'tidak valid',
                'message' => 'nama pengguna sudah ada'
            ],401);
        } elseif (!Gate::allows('admins')) {
            return response()->json(['status' => 'gagal', 'message' => 'anda bukan administrator'], 403);
        }

        $data = $request->only(['username', 'password']);
        // only() digunakan untuk mengambil var tertentu saja dan jika var kosong mak tidak diambil

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request['password']);
        }
        $user->update($data);

        return response()->json([
            'status' => 'berhasil',
            'username' => $user['username']
        ]);
    }

    public function deleteuser($id) {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'tidak ditemukan',
                'message' => 'pengguna tidak ditemukan'
            ],403);
        } elseif (!Gate::allows('admins')) {
            return response()->json([
                'status' => 'dilarang',
                'message' => 'anda bukan administrator'
            ], 403);
        }

        $user->delete();
        return response()->json([
            'status' => 'berhasil',
            'message' => 'pengguna telah dihapus'
        ],204);
    }
}

