<div class="bg-inherit">
  <h3 class="font-bold text-xl mb-4 flex gap-3 items-center justify-start">
    <p>@include('icons.check', ['width' => 50, 'height' => 50])</p>
    <p>Письмо отправлено!</p>
  </h3>
  <div class="">
    <div class=""></div>
    <div class="mb-3">Письмо с инструкциями по восстановлению пароля успешно отправлено на ваш электронный адрес. Пожалуйста, проверьте не только основной почтовый ящик, но также папку «Спам». Если возникнут вопросы, мы всегда готовы помочь!</div>
    <x-button class="w-full" x-on:click.prevent="() => {
        isOpen = false;
        const url = new URL(window.location);
        url.searchParams.delete('modal');
        window.history.replaceState(null, '', url);
      }
    ">Закрыть</x-button>
  </div>
</div>