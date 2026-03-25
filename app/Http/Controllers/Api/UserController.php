<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
  /**
   * Get authenticated user
   */
  public function show(Request $request)
  {
    return response()->json([
      'data' => $request->user()
    ]);
  }

  /**
   * Update authenticated user
   */
  public function update(Request $request)
  {
    $user = $request->user();

    $validated = $request->validate([
      'name' => 'sometimes|string|max:255',
      'email' => 'sometimes|email|unique:users,email,' . $user->id,
      'password' => 'sometimes|string|min:8|confirmed',
      'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:10240',
    ]);

    // Обновляем поля
    if (isset($validated['name'])) {
      $user->name = $validated['name'];
    }

    if (isset($validated['email'])) {
      $user->email = $validated['email'];
    }

    if (isset($validated['password'])) {
      $user->password = Hash::make($validated['password']);
    }

    // Обновляем аватар
    if ($request->hasFile('avatar')) {
      // Удаляем старый аватар если есть
      if ($user->avatar) {
        $oldPath = str_replace('/storage/', '', $user->avatar);
        Storage::disk('public')->delete($oldPath);
      }

      $path = $request->file('avatar')->store('avatars', 'public');
      $user->avatar = Storage::url($path);
    }

    $user->save();

    return response()->json([
      'data' => $user,
      'message' => 'User updated successfully'
    ]);
  }
}
