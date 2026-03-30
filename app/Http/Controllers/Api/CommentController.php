<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      return 'this is Comment';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
  public function update(Request $request, Comment $comment)
  {
    // Проверяем права через Gate
    if (Gate::denies('update-comment', $comment)) {
      return response()->json([
        'message' => 'У вас нет прав на редактирование этого комментария'
      ], 403);
    }

    // Валидация и обновление
    $validated = $request->validate([
      'content' => 'required|string|max:1000'
    ]);

    $comment->update($validated);

    return response()->json([
      'data' => $comment,
      'message' => 'Комментарий обновлен'
    ]);
  }

  public function destroy(Comment $comment)
  {
    // Проверяем права через Gate
    if (Gate::denies('delete-comment', $comment)) {
      return response()->json([
        'message' => 'У вас нет прав на удаление этого комментария'
      ], 403);
    }

    $comment->delete();

    return response()->json([
      'message' => 'Комментарий удален'
    ]);
  }
  private function updateFilmRating(){}
}
