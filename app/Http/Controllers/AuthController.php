<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (auth()->check()) {
            return auth()->user()->role === 'admin'
                ? redirect()->route('ctxh.dashboard')
                : redirect()->route('sinhvien.dashboard_sinhvien');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        // Trường hợp password CHƯA hash
        $user = User::where('username', $data['username'])->first();

        if (!$user || $user->password !== $data['password']) {
            return back()
                ->withErrors(['login' => 'Sai tên đăng nhập hoặc mật khẩu.'])
                ->withInput($request->only('username'));
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->role === 'admin') {
            return redirect()->route('ctxh.dashboard');
        }

        if ($user->role === 'sinhvien') {
            return redirect()->route('sinhvien.dashboard_sinhvien');
        }

        Auth::logout();

        return redirect()
            ->route('login')
            ->withErrors(['login' => 'Tài khoản không có quyền truy cập.']);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}