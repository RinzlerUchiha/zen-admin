<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        // Auth::logout();
        // if(Auth::check()){
        //     return redirect()->route('dashboard');
        // }
        
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required'
        ]);

        $credentials = [
            'U_Name' => $request->username, // Change 'user_name' if your DB column is different
            'password' => $request->password
        ];

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        // $user = User::where([
        //     'U_Name' => $request->username, 
        //     'U_Password' => $request->password
        // ])->first();

        // if($user /* && Hash::check($request->password, $user->U_Password) */)
        // {
        //     Auth::login($user, true);
        //     $request->session()->regenerate();
        //     return redirect()->intended(route('dashboard'));
        // }

        return back()
        ->withErrors(['error' => 'Invalid credentials'])
        ->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        // Optional: Clear custom remember token from database
        if ($user) {
            $user->remember_token = null;
            $user->save();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

}
