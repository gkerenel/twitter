<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
	public function store(Request $request, Post $post): JsonResponse
	{
		$validator = Validator::make($request->all(), [
			'content' => [ 'string', 'required', 'max:255' ],
		]);

		if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()->all()], 422);
		}

		$comment = new Comment();
		$comment['content'] = $request['content'];
		$comment['user_id'] = auth()->id();
		$comment['post_id'] = $post['id'];
		$comment->save();

		return response()->json($comment, 201);
	}

	public function update(Request $request, Comment $comment): JsonResponse
	{
		if ($comment['user_id'] !== auth()->id()) {
			return response()->json(['errors' => ['unauthorised']], 403);
		}

		$validator = Validator::make($request->all(), [
			'content' => ['string', 'required', 'max:255'],
		]);

		if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()->all], 422);
		}

		$comment['content'] = $request['content'];
		$comment->save();

		return response()->json($comment);
	}

	public function destroy(Comment $comment): Response | JsonResponse
	{
		if ($comment['user_id'] !== auth()->id())  {
			return response()->json(['errors' => ['unauthorised']], 403);
		}

		$comment->delete();
		return response()->noContent();
	}
}
