<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class LikeController extends Controller
{
	public function like(Post $post): Response | JsonResponse
	{
		$existing = Like::where('user_id', auth()->id())
			->where('post_id', $post['id'])
			->first();

		if ($existing) {
			return response()->json(['errors' => ['post already liked']], 422);
		}

		$like = new Like();
		$like['user_id'] = auth()->id();
		$like['post_id'] = $post['id'];
		$like->save();
		return response()->noContent();
	}

	public function unlike(Post $post): Response | JsonResponse
	{
		$like = Like::where('user_id', auth()->id())
			->where('post_id', $post['id'])
			->first();

		if (!$like) {
			return response()->json(['errors' => ['post not liked']], 422);
		}

		$like->delete();
		return response()->noContent();
	}
}
