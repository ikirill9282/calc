@extends('layout.site')

@section('content')
  <div class="px-2 sm:px-5 2xl:px-10 mb-4  bg-inherit">

      <x-card class="bg-inherit">
        <h3 class="font-bold text-xl mb-4">Восстановление пароля</h3>
          <form action="{{ route('password.change') }}" method="POST" class="flex flex-col justify-start items-stretch gap-4 bg-inherit max-w-2xl">
            @csrf
            <input type="hidden" name="user" value="{{ Crypt::encrypt($user->id) }}">

            <x-form.wrap label="Новый пароль" :show_password="true" showPasswordClass="show-password-btn" name="password" >
              <x-form.input 
                  type="password" 
                  required
                  aria-autocomplete="off"
                  autocomplete="new-password"
                  wire:model.live="reset.password"
                  name="password"
                  id="password"
                />
            </x-form.wrap>

            <x-form.wrap label="Повторите новый пароль" :show_password="true" showPasswordClass="show-password-btn" name="password_confirm" >
              <x-form.input 
                  required="required"
                  type="password" 
                  name='password_confirm'
                  id='password_confirm'
                  autocomplete='new-password'
                  wire:model.live='reset.password_confirm'
                />
            </x-form.wrap>


            <div class="flex justify-between items-stretch gap-2">
              <x-button class="">Изменить пароль</x-button>
            </div>
          </form>
      </x-card>
  </div>
@endsection