<div class="relative w-full h-full group/input">
    <div
    class="text-sm absolute top-0 left-4 px-2 translate-y-[-50%] text-primary-400 transition
          bg-white
          dark:bg-primary-900
          group-hover/input:text-secondary-600 dark:group-hover/input:text-secondary-400
          group-has-focus/input:text-secondary-600 dark:group-has-focus/input:text-secondary-400
          {{ $labelClass ?? '' }}
           @error($name ?? null) !text-red-500 @enderror
          ">
    {{ $label ?? '' }}
  </div>
  <textarea 
    {{ $attributes }}
    name="{{ $name ?? '' }}" 
    id="{{ $id ?? '' }}"
    class="w-full py-4 px-4 ring-0 outline-0 border transition
          placeholder:text-gray-400
          border-primary-200 dark:border-primary-400/50 h-28 sm:h-22 md:h-20 {{ $class ?? '' }}
          placeholder:text-gray-400 @error($name ?? null) !border-red-500 @enderror
          group-hover/input:border-secondary-600 dark:group-hover/input:border-secondary-400
          group-has-focus/input:border-secondary-600 dark:group-has-focus/input:border-secondary-400
          "
    placeholder="{{ $placeholder ?? '' }}"
    ></textarea>
</div>