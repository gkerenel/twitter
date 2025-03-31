<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [ 'name', 'username', 'email', 'password', 'bio', 'avatar' ];
    protected $hidden = [ 'password' ];

	public function posts(): HasMany
	{
		return $this->hasMany(Post::class);
	}

	public function comments(): HasMany
	{
		return $this->hasMany(Comment::class);
	}

	public function likes(): HasMany
	{
		return $this->hasMany(Like::class);
	}

	public function followers(): BelongsToMany
	{
		return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id');
	}

	public function following(): BelongsToMany
	{
		return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id');
	}
}
