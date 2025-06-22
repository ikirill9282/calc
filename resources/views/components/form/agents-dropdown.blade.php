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
])
<div class="dropdown-group relative {{ $class ?? '' }}" data-dropdown="{{ $name ?? '' }}">
  <x-form.input 
    placeholder="{{ $placeholder ?? '' }}"
    inputName="{{ $name ?? '' }}"
    label="{{ $label ?? '' }}"
    labelClass="{{ $labelClass ?? '' }}"
    class="input-off"
    value="{{ $this->getField($name) }}"
    :text="($this->getField($name) ? \App\Models\Agent::find($this->getField($name))->title : '')"
    :attrs="['data-filter' => $filter, 'data-search' => $search, 'data-name' => $name, 'autocomplete' => 'off']"
  />
  <div class="dropdown hidden absolute z-40 w-full left-0 bottom-0 translate-y-[100%] rounded-2xl shadow max-h-56 overflow-y-scroll bg-white dark:bg-black {{ $dropDownClass ?? '' }}">
    <div class="dropdown-wrap py-4 flex flex-col justify-start items-stretch">
      @if($items->isEmpty())
        <span class="px-4 py-1">Нет доступных контрагентов</span>
      @else
        @foreach($items as $item)
          <div 
            class="dropdown-item py-1.5 px-4 flex flex-col justify-start items-stretch hover:cursor-pointer hover:bg-primary-100 dark:hover:bg-primary-800 
            {{ ($this->getField('agent_id') == $item->id) ? 'bg-secondary-500/10 dark:bg-secondary-500/25' : '' }}"
            wire:click="clearFocusedAndSetField('agent_id', {{ $item->id }})"
            data-value="{{ $item->title }}"
            >
              <p class="text-md">{{ $item->title }}</p>
              {{-- <p class="text-xs sm:text-sm text-primary-500"></p> --}}
          </div>
        @endforeach
      @endif
    </div>
  </div>
</div>