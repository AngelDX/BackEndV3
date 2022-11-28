<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
//use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;
use App\Models\Person;

class AuthController extends Controller{

    public function signup(Request $request){

        $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed',
            'apellidos' => 'required|string',
        ]);

        //$validated = $request->validated();

        $persona = new Person([
            'nombres' => $request->name,
            'apellidos' => $request->apellidos,
        ]);
        $persona->save();


        $user = new User([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'people_id' => $persona->id,
        ]);
        $user->save();
        return response()->json([
            'message' => 'Successfully created user!'], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'       => 'required|string|email',
            'password'    => 'required|string',
            'remember_me' => 'boolean',
        ]);
        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized'], 401);
        }
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeeks(1);
        }
        $token->save();
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type'   => 'Bearer',
            'expires_at'   => Carbon::parse(
                $tokenResult->token->expires_at)
                    ->toDateTimeString(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' =>
            'Successfully logged out']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function users(){
        return response()->json(User::all());
    }
}
