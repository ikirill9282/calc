@props(['outlined', 'class'])

<button {{ $attributes }} class="px-4 py-3 text-center transition
              hover:cursor-pointer text-primary-100 bg-secondary-600 hover:bg-secondary-700
            
              {{ 
                (isset($outlined)) 
                ? '!bg-transparent border !text-primary-800/50 border-primary-800/50 hover:!text-secondary-600 hover:border-secondary-600 hover:!bg-secondary-600/15
                    dark:border-primary-100/50 dark:!text-primary-100/50 dark:hover:!text-secondary-400 dark:hover:border-secondary-400' 
                : '' 
              }}
              {{ $class ?? '' }}
              ">
  {{ $slot }}
</button>