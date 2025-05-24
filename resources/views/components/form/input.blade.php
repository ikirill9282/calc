  <div class="relative w-full h-full">
      <input type="hidden" name="{{ $name ?? '' }}">

      <div
          class="text-sm absolute top-0 left-4 px-2 translate-y-[-50%] text-primary-400
                bg-white
                dark:bg-primary-900
                {{ $labelClass ?? '' }}
                ">
          {{ $label ?? '' }}
      </div>

      <input id="{{ $inputId ?? '' }}" type="text" placeholder="{{ $placeholder ?? '' }}"
          class="w-full h-14 py-2 px-4 ring-0 outline-0 rounded-xl border
          border-primary-200 dark:border-primary-400/50 {{ $class ?? '' }}"
          
          @if(isset($attrs) && !empty($attrs))
            @foreach($attrs as $name => $value)
              {{ $name }}="{{ $value }}"
            @endforeach
          @endif
          >
      <div class="absolute top-[50%] right-4 translate-y-[-50%] input-clear">
          <div
              class="hover:cursor-pointer transition text-primary-500 hover:text-secondary-600 dark:hover:text-secondary-400">
              @include('icons.close')
          </div>
      </div>
  </div>
