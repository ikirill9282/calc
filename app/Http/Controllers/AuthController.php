<?php

namespace App\Http\Controllers;

use App\Mail\Reset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

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
        return redirect()->back();
      } catch (\Exception $e) {
        Log::error('Verification email error', ['error' => $e]);
        return redirect('/');
      }
      return redirect('/');
    }

    public function reset(Request $request)
    {

      if ($request->isMethod('post')) {
        $valid = $request->validate(['email' => 'required|string|email']);
        $user = User::where('email', $valid['email'])->first();
        if ($user) {
          Mail::to($user->email)->send(new Reset($user));
          $url = route('home') . '?modal=reset-sended';
          return redirect($url);
        }
      }

      if ($request->has('p') && !empty($request->get('p'))) {
        try {
          $params = Crypt::decrypt($request->get('p'));
          $user = User::find($params['id']);

          if (Carbon::now()->lte(Carbon::createFromTimestamp($params['expires']))) {
            return view('pages.reset', ['user' => $user]);
          }
        } catch (\Exception $e) {
          Log::error('Error when process param p', ['message' => $e->getMessage(), 'params' => $request->all()]);
        } catch (\Error $e) {
          Log::error('Error when process param p', ['message' => $e->getMessage(), 'params' => $request->all()]);
        }
      }

      return redirect('/?modal=password-reset');
    }

    public function change(Request $request)
    {

      $validator = Validator::make($request->all(), [
        'user' => 'required|string',
        'password' => 'required|string',
        'password_confirm' => 'required|string',
      ]);
      
      if ($validator->fails()) {
        return redirect()->back()->withErrors($validator->errors());
      }

      $valid = $validator->validate();
      $user = User::find(Crypt::decrypt($valid['user']));

      if (!User::validatePassword($valid['password'])) {
        return redirect()->back()->withErrors([
          'password' => 'Пароль должен состоять из букв и цифр верхнего и нижнего регистра и иметь длину не менее 6 символов',
        ]);
      }

      if ($valid['password'] !== $valid['password_confirm']) {
        return redirect()->back()->withErrors([
          'password' => 'Пароли не совпадают',
          'password_confirm' => 'Пароли не совпадают',
        ]);
      }

      $user->update(['password' => $valid['password']]);
      $url = route('home') . '?modal=reset-success';
      
      return redirect($url);
    }
}
