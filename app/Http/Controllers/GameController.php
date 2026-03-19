<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GameController extends Controller
{
    public function gameadd(Request $request) {
        $valid = Validator::make($request->all(),[
            'title' => 'required|min:3|max:60',
            'description' => 'required|max:200'
        ]);

        //generate id and slug
        do {
            $id = rand(1,63636363);
        } while (Game::where('id', $id)->exists());
        $slug = Str::slug($request['title'] . ' lks ' . strval($id), '_');

        if (!Gate::allows('admins') & !Gate::allows('developer')) {
            return response()->json([
                'status' => 'dilarang',
                'message' => 'anda tidak memiliki akses'
            ], 403);
        } elseif ($valid->fails()) {
            return response()->json($valid->errors(), 403);
        } elseif (Game::where('title', $request['title'])->exists()) {
            return response()->json([
                'status' => 'tidak valid',
                'message' => 'title game sudah ada'
            ], 400);
        }

        Game::create([
            'id' => $id,
            'title' => $request['title'],
            'slug' => $slug,
            'description' => $request['description'],
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'status' => 'berhasil',
            'slug' => $slug
        ],201);
    }
}
