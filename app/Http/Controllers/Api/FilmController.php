<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Models\Film;
use App\Services\FilmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate; // ← ДОБАВИТЬ ЭТОТ ИМПОРТ
use Symfony\Component\HttpFoundation\Response;

class FilmController extends Controller
{
  /**
   * @var FilmService
   */
  protected $filmService;

  public function __construct(FilmService $filmService)
  {
    $this->filmService = $filmService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $films = Film::paginate(6);
    return new SuccessResponse($films);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'imdb_id' => 'required|string|unique:films,imdb_id',
      'title' => 'required|string|max:255',
      'year' => 'required|string',
      'plot' => 'nullable|string',
      'poster' => 'nullable|string',
      'genre' => 'nullable|array'
    ]);

    $film = Film::create($validated);

    return new SuccessResponse($film, Response::HTTP_CREATED);
  }

  /**
   * Display the specified resource.
   */
  public function show($id)
  {
    $film = Film::find($id);

    if (!$film) {
      return new ErrorResponse(
        [],
        'Фильм не найден',
        Response::HTTP_NOT_FOUND
      );
    }

    return new SuccessResponse($film);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Film $film) // Используем Route Model Binding
  {
    if (Gate::denies('update-film', $film)) {
      return response()->json([
        'message' => 'У вас нет прав на обновление этого фильма'
      ], 403);
    }

    $validated = $request->validate([
      'title' => 'sometimes|string|max:255',
      'year' => 'sometimes|string',
      'plot' => 'nullable|string',
      'poster' => 'nullable|string',
      'genre' => 'nullable|array'
    ]);

    // Обновляем фильм
    $film->update($validated);

    return new SuccessResponse($film, Response::HTTP_OK);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Film $film) // ← Используем Route Model Binding вместо $id
  {
    // Проверяем права через Gate (опционально)
    if (Gate::denies('delete-film', $film)) {
      return response()->json([
        'message' => 'У вас нет прав на удаление этого фильма'
      ], 403);
    }

    $film->delete();

    return new SuccessResponse(null, Response::HTTP_NO_CONTENT);
  }

  /**
   * Поиск фильма по IMDB ID через OMDB API
   */
  public function searchByImdbId(string $imdbId)
  {
    try {
      $film = $this->filmService->findOrCreateFromApi($imdbId);

      if (!$film) {
        return new ErrorResponse(
          [],
          'Фильм с указанным IMDB ID не найден',
          Response::HTTP_NOT_FOUND
        );
      }

      return new SuccessResponse($film);

    } catch (\Exception $e) {
      return ErrorResponse::fromException($e);
    }
  }

  /**
   * Получение списка похожих фильмов
   */
  public function similar($id)
  {
    $film = Film::find($id);

    if (!$film) {
      return new ErrorResponse(
        [],
        'Фильм не найден',
        Response::HTTP_NOT_FOUND
      );
    }

    // Поиск похожих по жанру
    $genreToSearch = is_array($film->genre) && !empty($film->genre)
      ? $film->genre[0]
      : $film->genre;

    $similarFilms = Film::where('id', '!=', $id)
      ->when($genreToSearch, function ($query) use ($genreToSearch) {
        return $query->whereJsonContains('genre', $genreToSearch);
      })
      ->limit(5)
      ->get();

    return new SuccessResponse($similarFilms);
  }

  /**
   * Модерация фильма (добавляем метод из задания)
   */
  public function moderate(Request $request, Film $film)
  {
    // Проверяем, что пользователь - модератор или админ
    if (!auth()->user()->hasAnyRole(['moderator', 'admin'])) {
      return response()->json([
        'message' => 'Только модераторы могут модерировать фильмы'
      ], 403);
    }

    $validated = $request->validate([
      'status' => 'required|in:approved,rejected',
      'moderation_note' => 'nullable|string'
    ]);

    $film->update([
      'moderation_status' => $validated['status'],
      'moderated_by' => auth()->id(),
      'moderated_at' => now(),
      'moderation_note' => $validated['moderation_note'] ?? null
    ]);

    return new SuccessResponse($film, Response::HTTP_OK);
  }
}
