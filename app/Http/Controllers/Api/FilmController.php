<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Models\Film;
use App\Services\FilmService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Изменено с MovieService

class FilmController extends Controller
{
  /**
   * @var FilmService
   */
  protected $filmService;

  public function __construct(FilmService $filmService) // Изменено с MovieService
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
  public function update(Request $request, $id)
  {
    $film = Film::find($id);

    if (!$film) {
      return new ErrorResponse(
        [],
        'Фильм не найден',
        Response::HTTP_NOT_FOUND
      );
    }

    $validated = $request->validate([
      'imdb_id' => 'sometimes|string|unique:films,imdb_id,' . $id,
      'title' => 'sometimes|string|max:255',
      'year' => 'sometimes|string',
      'plot' => 'nullable|string',
      'poster' => 'nullable|string',
      'genre' => 'nullable|array'
    ]);

    $film->update($validated);

    return new SuccessResponse($film);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy($id)
  {
    $film = Film::find($id);

    if (!$film) {
      return new ErrorResponse(
        [],
        'Фильм не найден',
        Response::HTTP_NOT_FOUND
      );
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
      // Используем новый метод findOrCreateFromApi
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
}
