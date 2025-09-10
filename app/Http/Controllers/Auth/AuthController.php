<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                throw ValidationException::withMessages([
                    'email' => ['Credenciales incorrectas'],
                ]);
            }

            $user = $request->user();
            //token de acceso
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ]);
        }
    }


    public function logout(Request $request)
    {
        try {
            if (!$request->user()) {
                return response()->json([
                    'message' => 'Usuario no autenticado'
                ], 401); // 401 = Unauthorized
            }

            //elimina el token de acceso
            $request->user()->tokens()->delete();

            return response()->json([
                'message' => 'Sesión cerrada exitosamente'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => "Se generó un error: " . $th->getMessage(),
            ], 500);
        }
    }



    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                "email" => "required|email|exists:users,email",
                "new_password" => "required|min:8|confirmed"
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    "message" => "No existe un usuario con ese correo."
                ], 404);
            }

            if (Hash::check($request->new_password, $user->password)) {
                return response()->json([
                    'message' => 'error la contraseña no puede ser igual a la anterior'
                ], 422);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                "message" => "Contraseña actualizada exitosamente. Ahora puede iniciar sesion"
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al restablecer la contraseña',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
