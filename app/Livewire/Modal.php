<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class Modal extends Component
{
    public ?string $view;

    public array|null $manager = null;

    public $active = false;
    public $isOpen = false;

    public $showPassword = [];

    public $phone = null;
    
    public array $credentials = [
      'email' => null,
      'password' => null,
    ];

    public array $register = [
      'name' => null,
      'email' => null,
      'phone' => null,
      'password' => null,
      'password_confirm' => null,
    ];

    public array $reset = [
      'password' => null,
      'password_confirm' => null,
    ];

    public function mount(?string $view = 'auth')
    {
      $this->view = $view;
    }

    #[On('openManagerModal')]
    public function openManagerModal(mixed $manager = null)
    {
        $this->manager = $manager;
        $this->view = 'manager';
        $this->open();
    }

    #[On('openAuthModal')]
    public function openAuthModal()
    {
        $this->view = 'auth';
        $this->open();
    }

    #[On('openPasswordReset')]
    public function openPasswordReset()
    {
        $this->view = 'password-reset';
        $this->open();
    }

    #[On('openReset')]
    public function openReset()
    {
        $this->view = 'reset';
        $this->open();
    }

    #[On('openResetSend')]
    public function openResetSend()
    {
        $this->view = 'reset-sended';
        $this->open();
    }

    #[On('openResetSuccess')]
    public function openResetSuccess()
    {
        $this->view = 'reset-success';
        $this->open();
    }

    #[On('openRegister')]
    public function openRegister()
    {
      $this->view = 'register';
      $this->open();
    }

    #[On('modalOpen')]
    public function open()
    {
        $this->isOpen = true;
    }

    #[On('modalClose')]
    public function close()
    {
        $this->isOpen = false;
    }

    public function clearField($name)
    {
      $this->register[$name] = null;
      $this->credentials[$name] = null;
    }

    public function auth(string $fallback_url = '/')
    {
      try {
        // $valid = $this->validate($this->credentials, [
        //   'email' => 'required|string',
        //   'password' => 'required|string',
        // ]);

        $this->credentials = array_map('trim', $this->credentials);
        if (Auth::attempt($this->credentials, true)) {
          return redirect($fallback_url);
        }
      } catch (\Exception $e) {
        // dd($e);
        // dump('ok');
      }
      $this->addError('email', 'Неверный логин или пароль');

      return ;
    }
    
    public function reg()
    {
      $validator = Validator::make($this->register, [
          'name' => 'required|string',
          'email' => 'required|string|email:dns',
          'phone' => 'sometimes|nullable|string',
          'password' => 'required|string',
          'password_confirm' => 'required|string',
      ]);
      
      if ($validator->fails()) {
        $exception = new ValidationException($validator);
        // $exception->redirectTo($fallback_url);
        throw $exception;
      }

      $valid = $validator->validated();

      if (User::where('email', $valid['email'])->exists()) {
        $this->addError('email', 'Адрес электронной почты уже используется.');
        return ;
      }

      if (!User::validatePassword($valid['password'])) {
        $this->addError('password', 'Пароль должен состоять из букв и цифр верхнего и нижнего регистра и иметь длину не менее 6 символов');
        return ;
      }

      if ($valid['password'] !== $valid['password_confirm']) {
        $this->addError('password', 'Пароли не совпадают');
        $this->addError('password_confirm', 'Пароли не совпадают');
        return ;
      }

      try {
        $user_data = $this->register;
        unset($user_data['password_confirm']);

        $user = User::create($user_data);
        $user->sendEmailVerification();

        Auth::login($user);

        return redirect('/');
        
      } catch (\Exception $e) {
        Log::error('Register user error', [
          'data' => $this->register,
          'error' => $e,
        ]);
        $this->addError('modal', 'Что то пошло не так...');
        return ;
      }
    }

    public function setShowPassword(string $name)
    {
      if (array_key_exists($name, $this->showPassword)) {
        unset($this->showPassword[$name]);
      } else {
        $this->showPassword[$name] = true;
      }
    }

    public function render()
    {
      if (request()->has('modal')) {
        $this->view = request()->get('modal');
        $this->open();
      }
      return view('livewire.modal');
    }
}
