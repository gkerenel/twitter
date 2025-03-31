<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
	Route::post('/signin', 'signin');
	Route::post('/signup', 'signup');
});

Route::middleware('auth:sanctum')->group(function () {
	Route::post('/logout', [AuthController::class, 'logout']);

	Route::controller(AuthController::class)->group(function () {
		Route::delete('/signout', [AuthController::class, 'logout']);
	});

	Route::apiResource('posts', PostController::class);

	Route::controller(CommentController::class)->group(function () {
		Route::post('/posts/{post}/comments', 'store');
		Route::patch('/comments/{comment}', 'update');
		Route::delete('/comments/{comment}', 'destroy');
	});

	Route::controller(LikeController::class)->group(function () {
		Route::post('/posts/{post}/like', 'like');
		Route::delete('/posts/{post}/unlike', 'unlike');
	});

	Route::controller(UserController::class)->group(function () {
		Route::get('/users/search', 'search');
		Route::get('/users/{user}', 'profile');
		Route::patch('/users', 'update');
		Route::patch('/users/password', 'updatePassword');
		Route::delete('/users', 'destroy');

		Route::post('/users/{user}/follow', 'follow');
		Route::delete('/users/{user}/unfollow', 'unfollow');
	});

	Route::controller(FeedController::class)->group(function () {
		Route::get('/feed', 'index');
		Route::get('/explore', 'explore');
		Route::get('/hashtag/{hashtag}', 'hashtag');
	});
});
