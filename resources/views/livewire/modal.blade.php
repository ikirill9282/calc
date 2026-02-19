
<div 
    x-data="{ isOpen: @entangle('isOpen') }" 
    x-show="isOpen" 
    @keydown.escape.window="isOpen = false"
    x-on:click="(evt) => evt.target === document.querySelector('#modal') ? (isOpen = false) : null"
    class="fixed top-0 left-0 z-100 w-screen h-screen bg-primary-50/80 dark:bg-primary-900/80"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    style="display: none;"
>
  <div class="w-full h-full flex justify-center items-center" id="modal">
    <div class="relative max-w-xl w-full p-6 bg-white dark:bg-primary-800">
      <div class="absolute top-3 right-3 text-right mb-2 leading-0">
        <x-link
          x-on:click.prevent="() => {
            isOpen = false;
            const url = new URL(window.location);
            url.searchParams.delete('modal');
            window.history.replaceState(null, '', url);
          }
          "
          class="hover:cursor-pointer inline-block"
        >
          @include('icons.close')
        </x-link>
      </div>

      @error('modal')
        <div class="text-red-500 bg-red-500/10 max-w-[90%] text-xl font-bold mb-6 p-4 border ">{{ $message }}</div>
      @enderror

      @component("livewire.modal.{$this->view}")
        
      @endcomponent
    </div>
  </div>
</div>
