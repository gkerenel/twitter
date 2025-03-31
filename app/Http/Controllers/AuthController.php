<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signup(Request $request): JsonResponse
	{
		$validator = Validator::make($request->all(), [
			'name' => [ 'string', 'required', 'max:255', 'min:2'],
			'username' => [ 'string', 'required', 'max:255', 'min:2', 'unique:users' ],
			'email' => [ 'string', 'email', 'required', 'max:255', 'unique:users' ],
			'password' => [ 'string', 'required', 'max:72', 'min:8' ],
		]);

		if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()->all()], 422);
		}

		$user = User::create([
			'name' => $request['name'],
			'username' => $request['username'],
			'email' => $request['email'],
			'password' => Hash::make($request['password']),
		]);

		$token = $user->createToken('auth_token')->plainTextToken;

		return response()->json([
			'token' => $token,
			'user' => $user,
		], 201);
	}

	public function signin(Request $request): JsonResponse
	{
		$validator = Validator::make($request->all(), [
			'username' => [ 'string', 'required' ],
			'password' => [ 'string', 'required' ],
		]);

		if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()->all()], 422);
		}

		if (!Auth::attempt($request->only('username', 'password'))) {
			return response()->json(['errors' => ['invalid user credentials']], 401);
		}

		$user = User::where('username', $request['username'])->firstOrFail();
		$token = $user->createToken('auth_token')->plainTextToken;

		return response()->json([
			'user' => $user,
			'token' => $token
		]);
	}

	public function signout(Request $request): Response
	{
		$request->user()->currentAccessToken()->delete();
		return response()->noContent();
	}
}
