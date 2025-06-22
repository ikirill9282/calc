@props([
  'class', 
  'items' => collect([]),
  'placeholder',
  'name' => '',
  'label',
  'labelClass',
  'dropDownClass',
  'value' => null,
  'id',
  'filter' => false,
  'search' => false,
  'wire' => false,
  'model' => '',
])


<div class="dropdown-group relative {{ $class ?? '' }}" data-dropdown="{{ $name ?? '' }}">

  <x-form.input 
    placeholder="{{ $placeholder ?? '' }}"
    inputName="{{ $name ?? '' }}"
    label="{{ $label ?? '' }}"
    labelClass="{{ $labelClass ?? '' }}"
    value="{{ $this->getField($name) }}"
    :text="$this->getField($name)"
    :wire="$wire"
    :attrs="[
      'data-filter' => $filter, 
      'data-search' => $search, 
      'data-name' => $name, 
      'autocomplete' => 'off',
      // 'wire:model' => $model,
    ]"
    {{-- {{ isset($attributes['wire:model']) ? 'wire:model='.$attributes['wire:model'] : '' }} --}}
  />
  <div class="dropdown absolute z-40 w-full left-0 bottom-0 translate-y-[100%] 
              rounded-2xl shadow max-h-56 overflow-y-scroll bg-white dark:bg-black {{ $dropDownClass ?? '' }}
              {{ (($this->fields['user_focused_dropdown'] ?? null) == $name) ? '' : 'hidden' }}
            ">
    <div class="dropdown-wrap py-4 flex flex-col justify-start items-stretch">
      @if($items->isEmpty())
        <span class="px-4 py-1">Нет доступных адресов</span>
      @else
        @foreach($items as $item)
          <div 
            class="dropdown-item py-1.5 px-4 flex flex-col justify-start items-stretch hover:cursor-pointer hover:bg-primary-100 dark:hover:bg-primary-800 
            {{ ($this->getField($name) == (($item['wh'] ?? '').' '.($item['wh_address'] ?? ''))) ? 'bg-secondary-500/10 dark:bg-secondary-500/25' : '' }}"
            wire:click="clearFocusedAndSetField('{{ $name }}', '{{ (($item['wh'] ?? '').' '.($item['wh_address'] ?? '')) }}')"
            data-value="{{ $item['wh'] ?? '' }}"
            >
              <p class="text-md">{{ $item['wh'] ?? '' }}</p>
              <p class="text-xs sm:text-sm text-primary-500">{{ $item['wh_address'] ?? ''  }}</p>
          </div>
        @endforeach
      @endif
    </div>
  </div>
</div>