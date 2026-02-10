<div class="bg-inherit">
  <h3 class="font-bold text-xl mb-4">Восстановление пароля</h3>
  <form action="{{ route('password.reset') }}" method="POST" class="flex flex-col justify-start items-stretch gap-4 bg-inherit">
    @csrf

    <x-form.wrap label="Новый пароль" :show_password="true" name="password" >
      <x-form.input 
          type="{{ array_key_exists('password', $this->showPassword) ? 'text' : 'password' }}" 
          required
          aria-autocomplete="off"
          autocomplete="new-password"
          wire:model.live="reset.password"
          name="password"
          id="password"
        />
    </x-form.wrap>

    <x-form.wrap label="Повторите новый пароль" :show_password="true" name="password_confirm" >
      <x-form.input 
          required="required"
          type="{{ array_key_exists('password_confirm', $this->showPassword) ? 'text' : 'password' }}" 
          name='password_confirm'
          id='reg_password_confirm'
          autocomplete='new-password'
          wire:model.live='reset.password_confirm'
        />
    </x-form.wrap>


    <div class="flex justify-between items-stretch gap-2">
      <x-button wire:click.prevent="openAuthModal" outlined class="w-full">Назад</x-button>
      <x-button class="w-full">Изменить пароль</x-button>
    </div>
  </form>
</div>