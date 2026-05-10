<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\ActivationMail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function loginForm()
    {
        if (Auth::check()) return redirect()->route('notes.index');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Email hoặc mật khẩu không đúng.'])->withInput();
        }
        if (!$user->is_activated) {
            return back()->withErrors(['email' => 'Tài khoản chưa được kích hoạt. Vui lòng kiểm tra email.'])->withInput();
        }
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email hoặc mật khẩu không đúng.'])->withInput();
        }
        $request->session()->regenerate();
        return redirect()->route('notes.index')->with('welcome_back', auth()->user()->name);
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
        ], [
            'password.regex' => 'Mật khẩu phải có chữ HOA, chữ thường, số, và ký tự đặc biệt.',
        ]);

        $token = Str::random(64);
        $user  = User::create([
            'name'             => $request->name,
            'email'            => $request->email,
            'password'         => $request->password,
            'activation_token' => $token,
            'is_activated'     => false,
        ]);

        try {
            Mail::to($user->email)->send(new ActivationMail($user, $token));
        } catch (\Exception $e) { /* Mail lỗi vẫn cho tạo tài khoản */ }

        return redirect()->route('auth.login')->with('success', 'Đăng ký thành công! Kiểm tra email để kích hoạt.');
    }

    public function activate(Request $request)
    {
        $user = User::where('activation_token', $request->token)->first();
        if ($user) {
            $user->update(['is_activated' => true, 'activation_token' => null]);
            return redirect()->route('auth.login')->with('success', 'Tài khoản đã được kích hoạt!');
        }
        return redirect()->route('auth.login')->withErrors(['token' => 'Liên kết không hợp lệ.']);
    }

    public function forgotForm()
    {
        return view('auth.forgot');
    }

    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email'], [
            'email.exists' => 'Email không tồn tại trong hệ thống.',
        ]);

        $otp  = rand(100000, 999999);
        $user = User::where('email', $request->email)->first();
        $user->update(['reset_token' => $otp, 'reset_expires' => now()->addMinutes(15)]);

        try {
            Mail::to($user->email)->send(new ResetPasswordMail($user, $otp));
        } catch (\Exception $e) {}

        return redirect()->route('auth.reset')->with('reset_email', $request->email);
    }

    public function resetForm(Request $request)
    {
        if (!session('reset_email') && !$request->email) return redirect()->route('auth.forgot');
        return view('auth.reset', ['email' => session('reset_email', $request->email)]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email|exists:users,email',
            'otp'                   => 'required',
            'password'              => 'required|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)
                    ->where('reset_token', $request->otp)
                    ->where('reset_expires', '>=', now())
                    ->first();

        if (!$user) {
            return back()->withErrors(['otp' => 'Mã OTP không đúng hoặc đã hết hạn.']);
        }

        $user->update(['password' => $request->password, 'reset_token' => null, 'reset_expires' => null]);
        return redirect()->route('auth.login')->with('success', 'Đặt lại mật khẩu thành công!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('auth.login');
    }
}
