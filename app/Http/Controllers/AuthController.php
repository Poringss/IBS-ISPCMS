<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        // If already signed in, send to correct home
        if (Auth::check()) {
            $role = strtolower(trim(Auth::user()->role ?? 'client'));
            return redirect()->route(
                $role === 'admin'
                    ? 'dashboard'
                    : ($role === 'staff' ? 'staff.portal.dashboard' : 'client.home')
            );
        }
        // default "client" context login screen
        return view('auth.login', ['context' => 'client']);
    }

    // Admin login (same view, different context; blocks non-admin on submit)
    public function showAdminLogin()
    {
        if (Auth::check()) {
            $role = strtolower(trim(Auth::user()->role ?? 'client'));
            return redirect()->route(
                $role === 'admin'
                    ? 'dashboard'
                    : ($role === 'staff' ? 'staff.portal.dashboard' : 'client.home')
            );
        }
        return view('auth.login', ['context' => 'admin']);
    }

    // (Optional) Staff login page if you ever want /staff/login
    public function showStaffLogin()
    {
        if (Auth::check()) {
            $role = strtolower(trim(Auth::user()->role ?? 'client'));
            return redirect()->route(
                $role === 'admin'
                    ? 'dashboard'
                    : ($role === 'staff' ? 'staff.portal.dashboard' : 'client.home')
            );
        }
        return view('auth.login', ['context' => 'staff']);
    }

    public function login(Request $request)
    {
        $context = $request->input('context', 'client');

        $credentials = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'Invalid credentials'])
                ->onlyInput('email');
        }

        // Successful auth
        $request->session()->regenerate();

        // Remove any stale intended URL so role routing is clean
        $request->session()->forget('url.intended');

        $role = strtolower(trim(Auth::user()->role ?? 'client'));

        // If using the admin login page, only allow admins
        if ($context === 'admin' && $role !== 'admin') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()
                ->withErrors(['email' => 'This page is for admins only. Please use the correct login.'])
                ->onlyInput('email');
        }

        // Optional: if you add /staff/login and want to strictly enforce it
        if ($context === 'staff' && $role !== 'staff') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()
                ->withErrors(['email' => 'This page is for staff only. Please use the correct login.'])
                ->onlyInput('email');
        }

        // Role-based landing
        $route = $role === 'admin'
            ? 'dashboard'
            : ($role === 'staff' ? 'staff.portal.dashboard' : 'client.home');

        return redirect()->route($route);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        // also clear any leftover intended URL
        $request->session()->forget('url.intended');

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}
