<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Film;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
      Gate::define('update-comment', function ($user, Comment $comment) {
        // Может обновить: автор комментария ИЛИ пользователь с ролью модератора/админа
        return $user->id === $comment->user_id || $user->hasAnyRole(['moderator', 'admin']);
      });

      Gate::define('delete-comment', function ($user, Comment $comment) {
        // Может удалить: автор комментария ИЛИ пользователь с ролью модератора/админа
        return $user->id === $comment->user_id || $user->hasAnyRole(['moderator', 'admin']);
      });

      // Аналогично для фильмов, если нужно
      Gate::define('update-film', function ($user, Film $film) {
        return $user->hasAnyRole(['moderator', 'admin']);
      });

      Gate::define('delete-film', function ($user, Film $film) {
        return $user->hasAnyRole(['moderator', 'admin']);
      });
    }
}
