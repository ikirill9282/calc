<div class="bg-inherit">
  <h3 class="font-bold text-xl mb-8 flex gap-3 flex-col-reverse  items-center justify-start">
    <p class="py-4">@include('icons.check', ['width' => 75, 'height' => 75])</p>
    <p>Пароль успешно изменен!</p>
  </h3>
  <div class="flex justify-between items-center gap-4">
    <x-button class="w-full" outlined x-on:click.prevent="() => {
        isOpen = false;
        const url = new URL(window.location);
        url.searchParams.delete('modal');
        window.history.replaceState(null, '', url);
      }
    ">Закрыть</x-button>
    <x-button class="w-full" x-on:click.prevent="$dispatch('openAuthModal')">Войти</x-button>
  </div>
</div>