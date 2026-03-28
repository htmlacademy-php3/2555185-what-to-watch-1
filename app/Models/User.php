<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
  public function favorites()
  {
    return $this->hasMany(Favorite::class);
  }

  public function favoriteFilms()
  {
    return $this->belongsToMany(Film::class, 'favorites');
  }

  public function comments():HasMany
  {
    return $this->hasMany(Comment::class);
  }

  public function moderatedFilms():HasMany
  {
    return $this->hasMany(Film::class, 'created_by');
  }

  public function moderatorActions()
  {
    return $this->hasMany(ModeratorAction::class, 'moderator_id');
  }

public function roles()
{
  return $this->belongsToMany(Role::class);
}

// Вспомогательные методы для проверки ролей
  public function hasRole($slug)
  {
    return $this->roles()->where('slug', $slug)->exists();
  }

  public function hasAnyRole($slugs)
  {
    return $this->roles()->whereIn('slug', (array) $slugs)->exists();
  }
}
