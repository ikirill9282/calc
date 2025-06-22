@props([
    'items' => [],
    'label' => 'Куда:',
    'name' => null,
    '',
])

<div class="flex flex-col sm:flex-row sm:gap-4 distributor-group">
    <div class="py-4">{{ $label }}</div>
    <div class="flex flex-wrap justify-start items-start gap-3">
        @foreach ($this->getDistributors() as $item)
            @php
              $item = $item->toArray();   
            @endphp

            {{-- <div wire:click="setField('{{ $name }}', '{{ $item['distributor'] ?? null }}')" class="group flex distributor-item"> --}}
            <div class="group flex distributor-item">
                <input type="radio" name="{{ $name }}" value="{{ $item['distributor'] ?? '' }}"
                    class="w-0 h-0 leading-0 opacity-0"
                    {{ $attributes }}
                    @if($this->getField($name) == ($item['distributor'] ?? ''))
                      checked
                    @endif
                    {{-- {{ $this->getField($name) == ($item['distributor'] ?? '') ? 'checked' : '' }} --}}
                    >
                    {{-- px-4 py-2 sm:px-1 sm:py-2 --}}
                  <div class="flex justify-center items-center border rounded-xl hover:cursor-pointer transition
                    border-primary-700/10 bg-primary-700/10 hover:bg-secondary-600/10 hover:text-secondary-600 hover:border-secondary-600
                    group-has-checked:bg-secondary-400/10 group-has-checked:text-secondary-600 group-has-checked:border-secondary-600
                    dark:bg-primary-400/25 dark:hover:bg-secondary-600/20 dark:hover:text-secondary-400
                    group-has-checked:dark:bg-secondary-600/20 group-has-checked:dark:text-secondary-400">
                    @if (\Illuminate\Support\Facades\View::exists(strtolower("icons.{$item['distributor']}")))
                        <span class="p-2 pe-0">@include(strtolower("icons.{$item['distributor']}"), ['width' => 38, 'height' => 38])</span>
                    @endif
                    <div class="px-4 py-2">
                        {{ $item['distributor'] }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
