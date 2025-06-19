<div class="radio-group flex justify-start items-center hover:cursor-pointer">
    <input type="radio" id="{{ $id ?? '' }}" name="{{ $name ?? '' }}" value="{{ $value ?? '' }}" class="peer w-0"
        {{ $checked ?? '' }} />
    <div
        class="w-3 min-w-3 h-3 min-h-3 mr-3 rounded-full transition
        ring-1 ring-offset-3 dark:ring-offset-primary-900
        group-hover:ring-secondary-600 group-hover:dark:ring-secondary-400
      peer-checked:text-secondary-600 peer-checked:ring-secondary-600 peer-checked:dark:ring-secondary-400
    ">
        <div
            class="w-full h-full rounded-full scale-0 transition group-has-checked:scale-90 bg-primary-400 group-has-checked:bg-secondary-600 group-has-checked:dark:bg-secondary-400">
        </div>
    </div>
    <label for="{{ $id ?? '' }}" class="select-none hover:cursor-pointer">{{ $label ?? '' }}</label>
</div>
