<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordValidationController extends Controller
{
    public function validateToken(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 422);
        }

        $valid = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->where('created_at', '>=', now()->subHours(2))
            ->get()
            ->contains(function ($row) use ($request) {
                return Hash::check($request->token, $row->token);
            });
        if ($valid) {
            return response()->json(['valid' => true]);
        }
        return response()->json(['message' => 'Token de redefinição inválido.'], 422);
    }
}
