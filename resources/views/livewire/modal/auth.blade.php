@section('modalTitle')
  Войти в личный кабинет
@endsection

<div class="">
  <h3 class="font-bold text-xl mb-6">Войти в личный кабинет</h3>
  {{-- <p class="mb-5">Ссылка для сброса пароля будет отправлена на указанный вами электронный адрес. Просим проверить входящие сообщения, включая папку «Спам», и следовать инструкциям для установки нового пароля.</p> --}}
  <form wire:submit.prevent="auth" action="" class="flex flex-col justify-start items-stretch gap-6">
    @csrf
    <div class="">
        <x-form.input
          label="E-mail"
          labelClass="dark:!bg-primary-800"
          type="email"
          :wire="false"
          :attrs="[
            'reuquired' => 'required',
            'wire:model' => 'credentials.email',
            'name' => 'email',
          ]"
      />
      @error('email')
          <div class="text-red-500 mt-2 inline-block">{{ $message }}</div>
      @enderror
    </div>
    <x-form.input
      label="Пароль"
      labelClass="dark:!bg-primary-800"
      type="password"
      :wire="false"
      :attrs="[
        'required' => 'required',
        'wire:model' => 'credentials.password',
        'name' => 'password',
      ]"
    />
    
    <div class="flex justify-between">
      <x-link wire:click.prevent="openPasswordReset">Забыли пароль?</x-link>
      <x-link wire:click.prevent="openRegister">Регистрация</x-link>
    </div>
    <x-button>Войти</x-button>
    
  </form>
</div>