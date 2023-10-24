<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AvatarController extends Controller
{
    public function store(Request $request)
    {
        if (!$request->hasFile('avatar')){
            return response()->json(['error' => 'There is no image present'], 400);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'avatar' => 'required|file|image|mimes:jpg,png|max:1024'
        ]);

        $path = $request->file('avatar')->store('images', 'public');

        if (!$path){
            return response()->json(['error' => 'The file could not be saved'], 500);
        }
        
        $uploadedFile = $request->file('avatar');
        
        $user = User::find($request->user_id);

        $user->avatar = $uploadedFile->hashName();

        $user->save();

        return response()->json(['avatar' => $uploadedFile->hashName()], 200);
    }

}
