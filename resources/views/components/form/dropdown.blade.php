@props([
  'class', 
  'items' => collect([]),
  'placeholder',
  'name',
  'label',
  'labelClass',
  'dropDownClass',
  'value' => null,
  'id',
])

<div class="dropdown-group relative {{ $class ?? '' }}" data-dropdown="{{ $name ?? '' }}">
  <x-form.input 
    placeholder="{{ $placeholder ?? '' }}"
    inputName="{{ $name ?? '' }}"
    label="{{ $label ?? '' }}"
    labelClass="{{ $labelClass ?? '' }}"
    value="{{ $this->getField($name) }}"
    :text="$items->where('id', $this->getField($name))->first()?->title ?? $items->where('id', $this->getField($name))->first()['title'] ?? ''"
  />
  <div class="dropdown hidden absolute z-40 w-full left-0 bottom-0 translate-y-[100%] rounded-2xl shadow max-h-56 overflow-y-scroll bg-white dark:bg-black {{ $dropDownClass ?? '' }}">
    <div class="dropdown-wrap py-4 flex flex-col justify-start items-stretch">
      @if($items->isEmpty())
        <span class="px-4 py-1">Нет доступных адресов</span>
      @else
        @foreach($items as $item)
          @if(($item?->id ?? $item['id']) == '')
            @continue
          @endif
          <div 
            class="dropdown-item py-1.5 px-4 flex flex-col justify-start items-stretch hover:cursor-pointer hover:bg-primary-100 dark:hover:bg-primary-800 {{ $this->getField($name) == ($item?->id ?? $item['id']) ? 'bg-secondary-500/10 dark:bg-secondary-500/25' : '' }}"
            wire:click="setField('{{ $name }}', '{{ $item?->id ?? $item['id'] ?? null }}')"
            data-value="{{ $item?->title ?? $item['title'] }}"
            >
              <p class="text-md">{{ $item?->title ?? $item['title'] }}</p>
              <p class="text-xs sm:text-sm text-primary-500">{{ $item?->address ?? $item['address'] ?? ''  }}</p>
          </div>
        @endforeach
      @endif
    </div>
  </div>
</div>