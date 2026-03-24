<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\game_version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
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

    public function show($slug) {
        if (!Game::where('slug', $slug)->exists()) {
            return response()->json([
                'status' => 'tidak ditemukan',
                'message' => 'game tidak ditemukan'
            ], 404);
        }

        $game = Game::where('slug', $slug)->get()->map(fn ($game) => [
            'slug' => $game->slug,
            'title' => $game->title,
            'description' => $game->description,
            'thumbnail' => null,
            'created_at' => $game->created_at,
            'created_by' => $game->created_by,
            'scoreCount' => null,
            'gamePath' => null
        ]);

        return response()->json($game,200);
    }

    public function index(Request $request) {

        $page = $request->query('page', 0);
        $size = $request->query('size', 5); //(params, nilai default)
        $sortby = $request->query('sortBy', 'title');
        $sortdir =$request->query('sortDir', 'desc');

        $game = Game::orderBy($sortby, $sortdir)->skip($page * $size)->take($size)->get()->map(fn ($game) => [
            'slug' => $game->slug,
            'title' => $game->title,
            'description' => $game->description,
            'thumbnail' => null,
            'created_at' => $game->created_at,
            'created_by' => $game->created_by,
            'scoreCount' => null
        ]);

        // dd($game);

        return response()->json([
            'halaman' => $page,
            'ukuran' => $size,
            'totalElemen' => $size,
            'konten' => $game
        ]);
    }

    public function update(Request $request,$slug) {
        $game = Game::where('slug', $slug)->first();

        if (!$game) {
            return response()->json(['status' => 'tidak ditemukan'],404);
        } elseif ($game['created_by'] != $request->user()->id) {
            return response()->json([
                'status' => 'dilarang',
                'message' => 'anda bukan penulis game'
            ],403);
        }

        $data = $request->only(['title', 'description']);

        $game->update($data);

        return response()->json([
            'status' => 'keberhasilan'
        ]);
    }

    public function destroy(Request $request,$slug) {
        $game = Game::where('slug', $slug)->first();

        if (!$game) {
            return response()->json([
                'status' => 'game tidak ditemukan'
            ],404);
        } elseif ($game->created_by != $request->user()->id) {
            return response()->json([
                'status' => 'dilarang',
                'message' => 'anda bukan penulis game'
            ],403);
        }

        $game->delete();
        return response()->json([],204);
    }

    public function upload(Request $request,$slug) {

        $game = Game::where('slug', $slug)->first();
        $valid = Validator::make($request->all(),[
            'zipfile' => 'required|file|mimes:zip',
            'thumbnail' => 'sometimes|image|mimes:png,jpg,jpeg'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors(),403);
        } elseif (!$game) {
            return response()->json([
                'status' => 'tidak ditemukan'
            ],404);
        } elseif ($request->user()->id != $game->created_by) {
            return response()->json([
                'status' => 'dilarang',
                'message' => 'anda bukan penulis game'
            ],403);
        }

        do {
            $id = rand(1,63636336);
        } while (game_version::where('id', $id)->exists());

        $gvers = game_version::where('game_id', $game->id)->latest()->first(); //latest digunakan untuk mengurutkan dari yang awal
        if ($gvers) {
            $version = $gvers->version + 1;
        } else {
            $version = '1';
        }
        $filename = Str::random(20);

        $gamefile = $request->file('zipfile');
        $gamefile->storeAs('gamefile', $filename . '.' . 'zip');

        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $thumbnail->storeAs("/gamefile/thumbnail", $filename . '.' . $request->file('thumbnail')->getClientOriginalExtension());
        }
        // dd($game->id);

        game_version::create([
            'id' => $id,
            'game_id' => $game->id,
            'version' => $version,
            'storage_path' => "/gamefile/" . $filename
        ]);

        return response()->json([
            'status' => 'berhasil'
        ],201);

    }
}
