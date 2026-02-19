@props([
  'agent' => null,
  'view' => false,
])


@if($agent)

<x-card class="relative agents-block !py-3 {{ $view ? '!border-none mt-4 !px-3' : '' }}">   
  <div class="font-bold text-lg transition 
              {{ $view  ? '' : 'hover:cursor-pointer hover:text-secondary-600 dark:hover:text-secondary-400' }}
              ">
    @if(!$view)
      <div class="flex justify-between items-center title">
        <span>{{ $agent->title }}</span>
        {{-- <span class="{{ in_array($agent->id, $this->agents_open) ? 'rotate-180' : '' }}  icon transition duration-500 flex justify-between items-center"> --}}
        <span class="rotate-180 icon transition duration-500 flex justify-between items-center">
          @include('icons.arrow-toggle')
        </span>
      </div>
    @endif
  </div>
  {{-- <div class="{{ in_array($agent->id, $this->agents_open) ? '' : 'hidden' }} agents-toggle overflow-hidden"> --}}
  <div class="agents-toggle overflow-hidden">
      <div class="py-3 flex flex-col relative gap-2">
          <div class="flex justify-start items-start sm:items-center w-full sm:gap-2 flex-col sm:flex-row">
              <span class="font-bold basis-1/3 dark:text-primary-400 grow">ИНН:</span>
              <span class="basis-2/3">{{ $agent->inn }}</span>
          </div>
          <div class="flex justify-start items-start sm:items-center w-full sm:gap-2 flex-col sm:flex-row">
              <span class="font-bold basis-1/3 dark:text-primary-400 grow">ОГРН/ОГРНИП:</span>
              <span class="basis-2/3">{{ $agent->ogrn }}</span>
          </div>
          <div class="flex justify-start items-start sm:items-center w-full sm:gap-2 flex-col sm:flex-row">
              <span class="font-bold basis-1/3 dark:text-primary-400 grow">Юридический адрес:</span>
              <span class="basis-2/3">{{ $agent->address }}</span>
          </div>
          <div class="flex justify-start items-start sm:items-center w-full sm:gap-2 flex-col sm:flex-row">
              <span class="font-bold basis-1/3 dark:text-primary-400 grow">ФИО:</span>
              <span class="basis-2/3">{{ $agent->name }}</span>
          </div>
          <div class="flex justify-start items-start sm:items-center w-full sm:gap-2 flex-col sm:flex-row">
              <span class="font-bold basis-1/3 dark:text-primary-400 grow">Номер телефона:</span>
              <span class="basis-2/3">{{ $agent->phone }}</span>
          </div>
          <div class="flex justify-start items-start sm:items-center w-full sm:gap-2 flex-col sm:flex-row">
              <span class="font-bold basis-1/3 dark:text-primary-400 grow">Email:</span>
              <span class="basis-2/3">{{ $agent->email }}</span>
          </div>

          @if(!$view)
            <div class="flex gap-2 absolute bottom-3 right-0">
              <div wire:click.prevent="edit({{ $agent->id }})" class="hover:cursor-pointer hover:text-secondary-600 dark:hover:text-secondary-400">
                @include('icons.edit', ['width' => 20, 'height' => 20])
              </div>
              <div wire:click.prevent="delete({{ $agent->id }})" class="hover:cursor-pointer hover:text-secondary-600 dark:hover:text-secondary-400">
                @include('icons.delete', ['width' => 20, 'height' => 20])
              </div>
            </div>
          @endif
      </div>
  </div>
</x-card>
@endif