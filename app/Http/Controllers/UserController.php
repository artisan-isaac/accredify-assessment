<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {
            if (!Auth::attempt($request->only('email', 'password'))) {
                return back()->withErrors(['message' => 'Invalid credentials']);
            }

            $user = Auth::user();
            $token = $user->createToken('token')->plainTextToken;

            session(['token' => $token]);

            return redirect()->route('upload')->with('token', $token);
        }
        catch (\Throwable $th) {
            //throw $th;
        }
    }
}
