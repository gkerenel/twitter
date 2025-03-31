<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index(): JsonResponse
	{
		$posts = Post::with(['user', 'comments.user', 'likes'])
			->latest()
			->paginate(10);

		return response()->json($posts);
	}

	public function store(Request $request): JsonResponse
	{
		$validator = Validator::make($request->all(), [
			'content' => [ 'string', 'required', 'max:255' ],
			'media' => [ 'file', 'nullable', 'mimes:jpeg,png,jpg,gif,mp4', 'max:10240' ],
		]);

		if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()->all()], 422);
		}

		$post = new Post();
		$post['user_id'] = auth()->id();
		$post['content'] = $request['content'];

		if ($request->hasFile('media')) {
			$path = $request->file('media')->store('posts', 'public');
			$post['media_url'] = $path;
		}

		$post->save();
		return response()->json($post, 201);
	}

	public function update(Request $request, Post $post): JsonResponse
	{
		if ($post['user_id'] !== auth()->id()) {
			return response()->json(['errors' => ['unauthorised']], 403);
		}

		$validator = Validator::make($request->all(), [
			'content' => [ 'string', 'required', 'max:255' ],
			'media' => [ 'file', 'nullable', 'mimes:jpeg,png,jpg,gif,mp4', 'max:10240' ]
		]);

		if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()->all()], 422);
		}

		$post['content'] = $request['content'];

		if ($request->hasFile('media')) {
			$path = $request->file('media')->store('posts', 'public');
			$post['media_url'] = $path;
		}

		$post->save();
		return response()->json($post);
	}

	public function destroy(Post $post): Response | JsonResponse
	{
		if ($post['user_id'] !== auth()->id()) {
			return response()->json(['errors' => ['unauthorised']], 403);
		}

		$post->delete();
		return response()->noContent();
	}
}
