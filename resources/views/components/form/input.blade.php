  <div class="relative w-full h-full">
      <input id="{{ $inputId ?? '' }}" type="hidden" name="{{ $inputName ?? '' }}" value="{{ $value ?? '' }}">

      <div
          class="text-[11px] rounded sm:text-xs md:text-sm absolute top-0 left-4 px-2 translate-y-[-50%] text-primary-400
                bg-white
                dark:bg-primary-900
                {{ $labelClass ?? '' }}
                ">
          {{ $label ?? '' }}
      </div>

      <input id="{{ $id ?? ''}}" type="text" placeholder="{{ $placeholder ?? '' }}"
          class="input w-full min-h-14 py-2 ps-4 pe-12 ring-0 outline-0 rounded-xl border
          border-primary-200 dark:border-primary-400/50 
          placeholder:text-gray-400
          {{ $class ?? '' }}"
          value="{{ $text ?? '' }}"
          
          @if(isset($attrs) && !empty($attrs))
            @foreach ($attrs as $key => $value)
              {{ $key }}="{{ $value }}"
            @endforeach
          @endif
          >
      <div wire:click="clearField('{{ $inputName ?? '' }}')" class="absolute top-[50%] right-4 translate-y-[-50%] input-clear">
          <div
              class="hover:cursor-pointer transition text-primary-500 hover:text-secondary-600 dark:hover:text-secondary-400">
              @include('icons.close')
          </div>
      </div>
  </div>
