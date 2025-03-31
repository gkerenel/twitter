<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
	public function profile(User $user): JsonResponse
	{
		$user->load(['posts.comments', 'posts.likes']);

		return response()->json([
			'user' => $user,
			'followers_count' => $user->followers()->count(),
			'following_count' => $user->following()->count(),
		]);
	}

	public function update(Request $request): JsonResponse
	{
		$user = auth()->user();

		$validator = Validator::make($request->all(), [
			'name' => [ 'string', 'sometimes', 'max:255', 'min:2' ],
			'username' => [ 'string', 'sometimes', 'max:255', 'min:2', 'unique:users,username' . $user['id'] ],
			'email' => [ 'string', 'email', 'sometimes', 'max:255', 'unique:users,email' . $user['id'] ],
			'avatar'=> [ 'file', 'sometimes', 'mimes:jpeg,png,jpg', 'max:10240' ],
			'bio' => [ 'string', 'sometimes', 'max:255' ],
		]);

		if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()->all()], 422);
		}

		if ($request->has('name')) {
			$user['name'] = $request['name'];
		}

		if ($request->has('username')) {
			$user['username'] = $request['username'];
		}

		if ($request->has('email')) {
			$user['email'] = $request['email'];
		}

		if ($request->has('bio')) {
			$user['bio'] = $request['bio'];
		}

		if ($request->hasFile('avatar')) {
			$path = $request->file('avatar')->store('avatars', 'public');
			$user['avatar'] = $path;
		}

		$user->save();

		return response()->json($user);
	}

	public function updatePassword(Request $request): Response | JsonResponse
	{
		$validator = Validator::make($request->all(), [
			'current_password' => [ 'string', 'required' ],
			'password' => [ 'string', 'required', 'max:72', 'min:8', 'confirmed' ]
		]);

		if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()], 422);
		}

		$user = auth()->user();

		if (!Hash::check($request['current_password'], $user['password'])) {
			return response()->json(['errors' => ['current password is incorrect']], 401);
		}

		$user['password'] = Hash::make($request['password']);
		$user->save();

		return response()->noContent();
	}

	public function destroy(): Response
	{
		$user = auth()->user();
		$user->tokens()->delete();
		$user->delete();

		return response()->noContent();
	}

	public function search(Request $request): JsonResponse
	{
		$query = $request->get('query');

		$users = User::where('name', 'like', "%{$query}%")
			->orWhere('username', 'like', "%{$query}%")
			->paginate(10);

		return response()->json($users);
	}

	public function follow(User $user): Response | JsonResponse
	{
		if (auth()->id() === $user['id']) {
			return response()->json(['errors' => ['you cannot follow yourself']], 422);
		}

		$following = auth()->user()->following()->where('following_id', $user['id'])->exists();

		if ($following) {
			return response()->json(['errors' => ['already following this user']], 422);
		}

		auth()->user()->following()->attach($user['id']);

		return response()->noContent();
	}

	public function unfollow(User $user): Response | JsonResponse
	{
		$following = auth()->user()->following()->where('following_id', $user['id'])->exists();

		if (!$following) {
			return response()->json(['errors' => ['not following this user']], 422);
		}

		auth()->user()->following()->detach($user['id']);

		return response()->noContent();
	}
}
