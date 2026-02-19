@props([
  'items' => collect([]),
  'getOptionValueUsing' => null,
  'optionLabel' => null,
  'optionValue' => null,
  'optionDescription' => null,
  'optionKey' => null,
  'selectedKey' => null,
  'placeholder' => '',
  'empty_text' => 'Нет доступных адресов',
  'searchable' => false,
  'name' => '',
  'rp' => 'fields.',
  'readonly' => false,
  'inputClass' => '',
])
@php
  $wireModel = $attributes->get('wire:model');
  $fieldName = str_ireplace($rp, '', $wireModel);
  $dropdownState = $this?->dropdownOpen ?? [];
  $isDropdownOpen = array_key_exists($wireModel, $dropdownState) || (bool) \Illuminate\Support\Arr::get($dropdownState, $wireModel, false);
@endphp

<x-form.wrap :label="$attributes->get('label')" :name="$name">
  <div
    class="flex justify-start items-center relative w-full h-full hover:cursor-pointer dropdown-box"
    id="{{ $fieldName }}-dropdown"
    wire:click="openDropdown('{{ $wireModel }}')"
  >
    <x-form.input 
      type="{{ $searchable ? 'text' : 'hidden' }}"
      class="top-0 left-0 w-full h-full z-10 {{ $inputClass }}" 
      wire:model.live="{{ $attributes->get('wire:model') }}"
      name="{{ $name }}"
      autocomplete="off"
      placeholder="{{ $searchable ? $placeholder : '' }}"
    />
    
    @if(!$searchable)
      <div class="field relative z-20 min-h-9 flex justify-center items-center {{ empty(\Illuminate\Support\Arr::get($this->fields, $fieldName)) ? 'text-primary-600/80 dark:text-primary-300/80' : '' }}">
        @php
          $value = \Illuminate\Support\Arr::get($this->fields, $fieldName) ?? $placeholder;
          if ($optionValue == 'id') {
            $item = $items->where('id', $value)->first();
            $value = $item ? ($item?->$optionLabel ?? $value) : $value;
          }

        @endphp
        {{ $value ?? $placeholder }}
      </div>
    @endif

    <div class="dropdown absolute z-40 w-full left-0 bottom-[-5px] translate-y-[100%]
            shadow max-h-56 overflow-y-scroll bg-white dark:bg-black {{ $dropDownClass ?? '' }}
              @if (! $isDropdownOpen) hidden @endif
            "
          @if($searchable) data-searchable="true" @endif
          data-open="false"
          >
      <div class="dropdown-wrap py-4 flex flex-col justify-start items-stretch">
        @php
          if (is_array($items)) {
            $condition = empty($items);
          } else {
            $condition = $items?->isEmpty();
          }
        @endphp

        @if($condition)
          <span class="px-4 py-1">{{ $empty_text }}</span>
        @else
          @foreach($items as $item)
            @php
              if (is_string($item)) {
                $fieldValue = $item;
                $label = $item;
                $description = '';
              } else {
                $arr = is_array($item) ? $item : $item->toArray();
                $fieldValue = (!is_null($getOptionValueUsing)) ? $getOptionValueUsing($item) : $arr[$optionValue] ?? null;
                $label = $item[$optionLabel];
                $description = $item[$optionDescription] ?? '';
              }
            @endphp
            {{-- @dump($item[$optionKey] ?? false, $selectedKey, ($item[$optionKey] ?? false) == $selectedKey) --}}
            <div 
              class="dropdown-item py-1.5 px-4 flex flex-col justify-start items-stretch 
                hover:cursor-pointer hover:bg-primary-100 dark:hover:bg-primary-800
                @if($optionKey)
                  @if(($optionKey && $selectedKey) && (($item[$optionKey] ?? false) == $selectedKey))
                    bg-secondary-600/25 dark:bg-secondary-400/25 
                  @endif
                @elseif($fieldValue == $this->getField($fieldName))
                 bg-secondary-600/25 dark:bg-secondary-400/25 
                @endif
              "
              wire:click.prevent.stop="setField('{{ $wireModel }}', '{{ ($optionKey) ? $item[$optionKey] : $fieldValue }}')"
              >
                <p class="text-md">{{  $label }}</p>
                <p class="text-xs sm:text-sm text-primary-500">{{ $description }}</p>
            </div>
          @endforeach
        @endif
      </div>
    </div>
  </div>
</x-form.wrap>
