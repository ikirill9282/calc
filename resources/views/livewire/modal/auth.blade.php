@section('modalTitle')
  Войти в личный кабинет
@endsection

<div class="bg-inherit">
  <h3 class="font-bold text-xl mb-6">Войти в личный кабинет</h3>
  {{-- <p class="mb-5">Ссылка для сброса пароля будет отправлена на указанный вами электронный адрес. Просим проверить входящие сообщения, включая папку «Спам», и следовать инструкциям для установки нового пароля.</p> --}}
  <form wire:submit.prevent="auth" action="" class="flex flex-col justify-start items-stretch gap-6 bg-inherit">
    @csrf
    <div class="bg-inherit">
        <x-form.wrap label="E-mail">
          <x-form.input
            type="email"
            name="email"
            wire:model="credentials.email"
            reuquired="reuquired"
            aria-autocomplete="off"
            autocomplete="off"
          />
        </x-form.wrap>
      @error('email')
          <div class="text-red-500 mt-2 inline-block">{{ $message }}</div>
      @enderror
    </div>
    <x-form.wrap label="Пароль">
      <x-form.input
        type="password"
        wire:model="credentials.password"
        name="password"
        reuquired="reuquired"
        aria-autocomplete="off"
        autocomplete="off"
      />
    </x-form.wrap>
    
    <div class="flex justify-between">
      <x-link wire:click.prevent="openPasswordReset">Забыли пароль?</x-link>
      <x-link wire:click.prevent="openRegister">Регистрация</x-link>
    </div>
    <x-button>Войти</x-button>
    
  </form>
</div>