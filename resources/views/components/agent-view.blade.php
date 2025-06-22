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
          <div class="flex justify-start items-center w-full">
              <span class="font-bold basis-1/3 dark:text-primary-400">ИНН:</span>
              <span>{{ $agent->inn }}</span>
          </div>
          <div class="flex justify-start items-center w-full">
              <span class="font-bold basis-1/3 dark:text-primary-400">ОГРН/ОГРНИП:</span>
              <span>{{ $agent->ogrn }}</span>
          </div>
          <div class="flex justify-start items-center w-full">
              <span class="font-bold basis-1/3 dark:text-primary-400">Юридический адрес:</span>
              <span>{{ $agent->address }}</span>
          </div>
          <div class="flex justify-start items-center w-full">
              <span class="font-bold basis-1/3 dark:text-primary-400">ФИО:</span>
              <span>{{ $agent->name }}</span>
          </div>
          <div class="flex justify-start items-center w-full">
              <span class="font-bold basis-1/3 dark:text-primary-400">Номер телефона:</span>
              <span>{{ $agent->phone }}</span>
          </div>
          <div class="flex justify-start items-center w-full">
              <span class="font-bold basis-1/3 dark:text-primary-400">Email:</span>
              <span>{{ $agent->email }}</span>
          </div>

          @if(!$view)
            <div wire:click.prevent="edit({{ $agent->id }})" class="absolute bottom-3 right-0 hover:cursor-pointer hover:text-secondary-600 dark:hover:text-secondary-400">
              @include('icons.edit', ['width' => 20, 'height' => 20])
            </div>
          @endif
      </div>
  </div>
</x-card>
@endif