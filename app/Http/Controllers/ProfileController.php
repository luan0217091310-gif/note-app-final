<?php

namespace App\Http\Controllers;

use App\Models\Label;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $user   = Auth::user();
        $labels = Label::where('user_id', $user->id)->get();
        return view('profile.index', compact('user', 'labels'));
    }

    public function update(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $user = Auth::user();
        $data = ['name' => $request->name];

        if ($request->hasFile('avatar')) {
            $request->validate(['avatar' => 'image|max:2048']);
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = $request->file('avatar')->store('uploads/avatars', 'public');
        }

        $user->update($data);
        return redirect()->route('profile.index')->with('success', 'Cập nhật hồ sơ thành công!');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password'          => 'required',
            'password'              => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();
        if (!Hash::check($request->old_password, $user->password)) {
            return redirect()->route('profile.index')->withErrors(['old_password' => 'Mật khẩu cũ không đúng.']);
        }

        $user->update(['password' => $request->password]);
        return redirect()->route('profile.index')->with('success', 'Đổi mật khẩu thành công!');
    }

    public function updatePreferences(Request $request)
    {
        $data = $request->json()->all();
        Auth::user()->update([
            'font_size'  => $data['font_size']  ?? 'medium',
            'note_color' => $data['note_color']  ?? '#ffffff',
            'theme'      => $data['theme']       ?? 'light',
        ]);
        return response()->json(['success' => true]);
    }
}
