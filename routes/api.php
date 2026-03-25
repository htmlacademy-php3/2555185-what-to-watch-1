<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\FilmController;
use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\PromoController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
| Публичные маршруты
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/films', [FilmController::class, 'index']);
Route::get('/films/{film}', [FilmController::class, 'show']);
Route::get('/films/{film}/similar', [FilmController::class, 'similar']);
Route::get('/comments/{film}', [CommentController::class, 'index']);
Route::get('/genres', [GenreController::class, 'index']);
Route::get('/promo', [PromoController::class, 'show']);

/*
| Маршруты для аутентифицированных пользователей
*/
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/user', [UserController::class, 'show']);
  Route::patch('/user', [UserController::class, 'update']);
  Route::post('/logout', [AuthController::class, 'logout']);

  // Управление избранным
  Route::get('/favorite', [FavoriteController::class, 'index']);
  Route::post('/films/{film}/favorite', [FavoriteController::class, 'store']);
  Route::delete('/films/{film}/favorite', [FavoriteController::class, 'destroy']);

  // Управление комментариями
  Route::post('/comments/{film}', [CommentController::class, 'store']);
  Route::patch('/comments/{comment}', [CommentController::class, 'update']);
  Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
});
Route::middleware(['auth:sanctum', 'role:admin,moderator'])->group(function () {
  Route::post('/films', [FilmController::class, 'store']);
  Route::put('/films/{film}', [FilmController::class, 'update']);
  Route::delete('/films/{film}', [FilmController::class, 'destroy']);
  Route::post('/films/{film}/moderate', [FilmController::class, 'moderate']);
});
