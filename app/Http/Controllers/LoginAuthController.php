<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ormawa;

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

        if ($role === 'kemahasiswaan') {
            if (Auth::guard('kemahasiswaan')->attempt($credentials)) {
                return redirect()->route('kemahasiswaan.dashboard');
            }
        }

        $password = $request->input('password');

        switch ($role) {
            case 'admin':
                $credentials = ['email' => $request->input('email'), 'password' => $password];
                $guard = 'admin';
                $redirect = '/admin/dashboard';
                break;
            case 'ormawa':
                $credentials = ['nim' => $request->input('nim'), 'password' => $password];
                $guard = 'ormawa';
                $redirect = '/ormawa/dashboard';
                break;
            case 'dosen':
                $credentials = ['nip' => $request->input('nip'), 'password' => $password];
                $guard = 'dosen';
                $redirect = '/dosen/dashboard';
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

        if ($role === 'ormawa') {
            return back()->withErrors(['login' => 'NIM atau password salah, tolong masukkan ulang.']);
        } elseif ($role === 'dosen') {
            return back()->withErrors(['login' => 'NIP atau password salah, tolong masukkan ulang.']);
        }

        if ($request->expectsJson()) {
            return response()->json(['status' => 'error', 'message' => 'Kredensial tidak valid'], 401);
        }

        return back()->withErrors(['login' => 'Kredensial tidak valid']);
    }

    public function logout(Request $request)
    {
        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
