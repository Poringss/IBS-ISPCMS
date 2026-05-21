<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class SettingsController extends Controller
{
    // Settings page (My Password + Admin Reset tab if role=admin)
    public function index()
    {
        return view('settings.index');
    }

    // Admin: fetch users for dropdown
    public function users()
    {
        $this->authorizeAdmin();

        return User::select('id','name','email','role')
            ->orderBy('role')->orderBy('name')
            ->get();
    }

    // Any logged-in user: change own password (requires current password)
    public function updateOwnPassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required','confirmed', Password::min(8)],
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput();
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Your password has been updated.');
    }

    // Admin: reset someone else’s password (Admin/Staff/Client users live in users table)
    public function adminResetPassword(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'user_id' => ['required','exists:users,id'],
            'password' => ['required','confirmed', Password::min(8)],
        ]);

        $target = User::findOrFail($request->user_id);
        $target->password = Hash::make($request->password);
        $target->save();

        return back()->with('success', "Password reset for {$target->name} ({$target->role}).");
    }

    private function authorizeAdmin(): void
    {
        $u = Auth::user();
        if (! $u || strtolower((string)$u->role) !== 'admin') {
            abort(403, 'Only admins can perform this action.');
        }
    }
}
