<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('nip', 'password');
        $role = $request->input('role');

        $password = $request->input('password');

        switch ($role) {
            case 'admin':
                $credentials = ['nip' => $request->input('nip'), 'password' => $password];
                $guard = 'admin';
                $redirect = '/admin/dashboard';
                break;
            case 'kuwu':
                $credentials = ['nip' => $request->input('nip'), 'password' => $password];
                $guard = 'kuwu';
                $redirect = '/kuwu/dashboard';
                break;
            default:
                return back()->withErrors(['role' => 'Role tidak valid']);
        }

        if (Auth::guard($guard)->attempt($credentials)) {
            if ($request->expectsJson()) {
                $user = Auth::guard($guard)->user();
                $token = bcrypt($user->id);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Login berhasil',
                    'user' => $user,
                    'role' => $role,
                    'token' => $token
                ]);
            }

            return redirect()->intended($redirect);
        }

        // Error message for both admin and kuwu since they use the same credentials
        return back()->withErrors(['login' => 'NIP atau password salah, tolong masukkan ulang.']);
    }

    public function logout(Request $request)
    {
        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
