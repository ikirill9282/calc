<div class="relative w-full h-full">
    <div
    class="text-sm absolute top-0 left-4 px-2 translate-y-[-50%] text-primary-400
          bg-white
          dark:bg-primary-900
          {{ $labelClass ?? '' }}
          ">
    {{ $label ?? '' }}
  </div>
  <textarea 
    name="{{ $name ?? '' }}" 
    id="{{ $id ?? '' }}"
    class="w-full py-4 px-4 ring-0 outline-0 rounded-xl border
          placeholder:text-gray-400
          border-primary-200 dark:border-primary-400/50 {{ $class ?? '' }}"
    rows="{{ $rows ?? 2 }}"
    placeholder="{{ $placeholder ?? '' }}"
    ></textarea>
</div>