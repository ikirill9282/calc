<input 
  {{ $attributes->filter(fn($k, $v) => $v !== 'class') }} 
  class="outline-0 h-full w-full {{ $attributes->get('class') }}" 
/>