<div class="max-w-md w-full mx-auto flex justify-center items-center dark:bg-primary-800">
  @if (empty($this->manager))
    <p>Пожалуйста, выберите адрес распределительного центра</p>
  @else
    <div class="w-full flex flex-col justify-start items-stretch gap-3 text-lg">
      @foreach($this->manager as $key => $value)
        @if(in_array($key, ['name', 'email', 'phone']))
          <p class="grid grid-cols-[80px_1fr]">
            <span>
              @switch($key)
                @case('name')
                    ФИО:
                    @break
                @case('email')
                    Email:
                    @break
                @case('phone')
                    Phone:
                    @break   
              @endswitch
            </span>
            <span>
              @switch($key)
                @case('name')
                    {{ $value }}
                    @break
                @case('email')
                    <x-link href="mailto:{{ $value }}">{{ $value }}</x-link>
                    @break
                @case('phone')
                    <x-link href="phone:{{ preg_replace('/[^0-9]+/is', '', $value)}}">{{ $value }}</x-link>
                    @break   
              @endswitch
            </span>
          </p>
        @endif
      @endforeach
    </div>
  @endif
</div>