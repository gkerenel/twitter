<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedController extends Controller
{
	public function index(Request $request): JsonResponse
	{
		$user = auth()->user();
		$followingIds = $user->following()->pluck('users.id');
		$userIds = $followingIds->push($user->id);

		$posts = Post::whereIn('user_id', $userIds)
			->with(['user', 'comments.user', 'likes'])
			->latest()
			->paginate(15);

		$posts->getCollection()->transform(function ($post) use ($user) {
			$post->liked = $post->likes->contains('user_id', $user->id);
			$post->likes_count = $post->likes->count();
			$post->comments_count = $post->comments->count();

			unset($post->likes);
			return $post;
		});

		return response()->json($posts);
	}

	public function explore(Request $request): JsonResponse
	{
		$user = auth()->user();

		$posts = Post::withCount(['likes', 'comments'])
			->with(['user'])
			->orderBy('likes_count', 'desc')
			->orderBy('comments_count', 'desc')
			->orderBy('created_at', 'desc')
			->paginate(15);

		$posts->getCollection()->transform(function ($post) use ($user) {
			$post->liked = DB::table('likes')
				->where('post_id', $post->id)
				->where('user_id', $user->id)
				->exists();

			return $post;
		});

		return response()->json($posts);
	}

	public function hashtag(Request $request, $hashtag): JsonResponse
	{
		$user = auth()->user();

		$hashtag = ltrim($hashtag, '#');

		$posts = Post::where('content', 'like', "%#$hashtag%")
			->with(['user'])
			->withCount(['likes', 'comments'])
			->latest()
			->paginate(15);

		$posts->getCollection()->transform(function ($post) use ($user) {
			$post->liked = DB::table('likes')
				->where('post_id', $post->id)
				->where('user_id', $user->id)
				->exists();

			return $post;
		});

		return response()->json([
			'hashtag' => $hashtag,
			'posts' => $posts
		]);
	}
}
