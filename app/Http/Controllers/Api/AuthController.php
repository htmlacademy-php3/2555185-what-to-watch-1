<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
  /**
   * Регистрация пользователя
   */
  public function register(Request $request): JsonResponse
  {
    $validated = $request->validate([
      'name' => 'required|max:255',
      'email' => 'required|email|unique:users',
      'password' => 'required|min:8|confirmed',
      'avatar' => 'sometimes|image|max:10240',
    ]);

    $data = [
      'name' => $validated['name'],
      'email' => $validated['email'],
      'password' => Hash::make($validated['password']),
      'role' => 'user',
    ];

    // Сохранение аватара
    if ($request->hasFile('avatar')) {
      $path = $request->file('avatar')->store('avatars', 'public');
      $data['avatar'] = Storage::url($path);
    }

    $user = User::create($data);

    // Создание токена
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
      'data' => [
        'user' => $user,
        'token' => $token
      ]
    ], 201);
  }

  /**
   * Аутентификация пользователя
   */
  public function login(Request $request): JsonResponse
  {
    $request->validate([
      'email' => 'required|email',
      'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
      throw ValidationException::withMessages([
        'email' => ['Неверные учетные данные.'],
      ]);
    }

    // Удаляем все старые токены пользователя
    $user->tokens()->delete();

    // Создаем новый токен
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
      'data' => [
        'user' => $user,
        'token' => $token
      ]
    ]);
  }

  /**
   * Выход пользователя
   */
  public function logout(Request $request): JsonResponse
  {
    // Удаляем текущий токен
    $request->user()->currentAccessToken()->delete();

    return response()->json([
      'message' => 'Успешный выход из системы'
    ]);
  }
}
