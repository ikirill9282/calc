  <div class="relative w-full h-full group/input">
      <input 
        {{ isset($inputId) && $inputId ? "id='$inputId'" : '' }} 
        type="hidden" 
        name="{{ ($wire ?? true) ? ($inputName ?? '') : '' }}" 
        value="{{ $value ?? '' }}"
      >

      <div
          class="text-[11px] rounded sm:text-xs md:text-sm absolute top-0 left-4 px-2 translate-y-[-50%] text-primary-400 transition
                bg-white 
                dark:bg-primary-900
                group-hover/input:text-secondary-600 dark:group-hover/input:text-secondary-400
                group-has-focus/input:text-secondary-600 dark:group-has-focus/input:text-secondary-400
                @error($attrs['name'] ?? $inputName ?? null) !text-red-500 @enderror
                {{ $labelClass ?? '' }}
                ">
          {{ $label ?? '' }}
      </div>
      
      {{-- @dump($text ?? 'not') --}}
      <input {{ isset($attributes['x-mask']) ? "x-mask={$attributes['x-mask']}" : '' }} {{ isset($id) && $id ? "id='$id'" : '' }} type="{{ $type ?? 'text' }}" placeholder="{{ $placeholder ?? '' }}"
          class="input w-full h-14 py-2 ps-4 pe-12 ring-0 outline-0 rounded-xl border transition
          border-primary-200 dark:border-primary-400/50 
          placeholder:text-gray-400 @error($attrs['name'] ?? $inputName ?? null) !border-red-500 @enderror
          group-hover/input:border-secondary-600 dark:group-hover/input:border-secondary-400
          focus:border-secondary-600 dark:focus:border-secondary-400
          {{ $class ?? '' }}"
          value="{{ $text ?? '' }}"
          name="{{ ($wire ?? true) ? 'unknown' : ($inputName ?? 'unknown') }}"
          {{ isset($attributes['wire:model']) ? "wire:model={$attributes['wire:model']}" : '' }}
          
          @if(isset($attrs) && !empty($attrs))
            @foreach ($attrs as $key => $value)
              {{ $key }}="{{ $value }}"
            @endforeach
          @endif
          >
      <div class="absolute top-3.5 right-4 {{ ($wire ?? true) ? 'input-clear' : 'clear-input' }}" data-name="{{ $inputName ?? $attrs['name'] ?? null }}">
          <div
              class="hover:cursor-pointer transition text-primary-500 hover:text-secondary-600 dark:hover:text-secondary-400">
              @include('icons.close')
          </div>
      </div>
      @error($attrs['name'] ?? $inputName ?? null)
        <div class="mt-3 text-red-500">
          {{ $message }}
        </div>
      @enderror
  </div>
