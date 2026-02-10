<div class="bg-inherit">
  <h3 class="font-bold text-xl mb-4">Восстановление пароля</h3>
  <p class="mb-5">Ссылка для сброса пароля будет отправлена на указанный вами электронный адрес. Просим проверить входящие сообщения, включая папку «Спам», и следовать инструкциям для установки нового пароля.</p>
  <form action="{{ route('password.reset') }}" method="POST" class="flex flex-col justify-start items-stretch gap-4 bg-inherit">
    @csrf
    <x-form.wrap label="E-mail">
      <x-form.input
        type="email"
        name="email"
        required
      />
    </x-form.wrap>


    <div class="flex justify-between items-stretch gap-2">
      <x-button wire:click.prevent="openAuthModal" outlined class="w-full">Назад</x-button>
      <x-button class="w-full">Отправить ссылку</x-button>
    </div>
  </form>
</div>