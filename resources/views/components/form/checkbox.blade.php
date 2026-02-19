@props([
  'id' => uniqid(),
  'name' => uniqid(),
  'label' => null,
  'class' => '',
  'checked',
])

<div class="checkbox-form-group flex justify-start items-center group hover:cursor-pointer">
  <input type="checkbox" id="{{ $id }}" name="{{ $name }}" class="w-0 peer" {{ $attributes }} >
  <div 
    class="checkbox-square w-4.5 h-4.5 border mr-2 transition
     peer-checked:bg-secondary-600 peer-checked:border-secondary-600 group-hover:border-secondary-600 
     peer-checked:dark:bg-secondary-400 peer-checked:dark:border-secondary-400 group-hover:dark:border-secondary-400
    {{ $class }}"
    >
    <div class="flex justify-center items-center w-full h-full scale-0 group-has-checked:scale-100 group-has-checked:text-white">
      @include('icons.check-sm', ['width' => 15, 'height' => 15])
    </div>
  </div>
  <label for="{{ $id }}" class="transition select-none hover:cursor-pointer">{{ $label }}</label>
</div>