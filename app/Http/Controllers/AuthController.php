<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            Session::regenerate();
            return redirect()->back();
        }

        return redirect()->back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        Session::regenerate();

        return redirect()->route('home');
    }

    public function verify(Request $request, string $hash)
    {
      try {
        $id = Crypt::decrypt($hash);
        $user = User::find($id);

        if (!empty($user->email_verified_at)) {
          User::where('id', $id)->update(['email_verified_at' => Carbon::now()]);
        }

      } catch (\Exception $e) {
        Log::error('Verification email error', ['error' => $e]);
        return redirect('/');
      }
      return redirect('/');
    }

    public function reset(Request $request)
    {
      dd('pk');
    }
}
