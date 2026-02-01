<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        // If already logged in, redirect to appropriate dashboard
        if (Auth::check()) {
            return $this->redirectToDashboard(Auth::user());
        }

        return view('login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Vui lòng nhập tên đăng nhập',
            'password.required' => 'Vui lòng nhập mật khẩu',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only('username'));
        }

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];

        // Check if user exists first to provide specific error for disabled accounts
        $user = User::where('username', $request->username)->first();
        if ($user && !$user->active && \Hash::check($request->password, $user->password)) {
            return redirect()->back()
                ->withErrors(['username' => 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ Admin.'])
                ->withInput($request->only('username'));
        }

        // Add active check for Auth::attempt
        $credentials['active'] = true;

        // Attempt to log the user in
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Redirect based on role
            return $this->redirectToDashboard($user);
        }

        // Authentication failed
        return redirect()->back()
            ->withErrors(['username' => 'Tên đăng nhập hoặc mật khẩu không chính xác'])
            ->withInput($request->only('username'));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Đăng xuất thành công');
    }

    /**
     * Redirect user to appropriate dashboard based on role
     */
    protected function redirectToDashboard(User $user)
    {
        switch ($user->role_code) {
            case User::ROLE_ADMIN:
                return redirect()->route('admin.dashboard');

            case User::ROLE_DEPARTMENT:
                return redirect()->route('department.dashboard');

            case User::ROLE_BUYER:
                return redirect()->route('buyer.dashboard');

            default:
                // Fallback to department dashboard
                return redirect()->route('department.dashboard');
        }
    }
}
