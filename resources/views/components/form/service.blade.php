<div class="flex flex-col sm:flex-row sm:gap-4 distributor-group">
  <input type="hidden" name="distributor">
  <div class="py-4">{{ $label ?? 'Куда:' }}</div>
  <div class="flex flex-wrap justify-start items-start gap-3">
    @foreach (\App\Models\Distributor::all() as $distributor)
      <div class="group flex distributor-item">
        <input type="radio" name="distributor_id" value="{{ $distributor->id }}" class="w-0 h-0 leading-0">
        <div class="px-4 py-2 sm:px-6 sm:py-4 border rounded-xl hover:cursor-pointer transition
                  border-primary-700/10 bg-primary-700/10 hover:bg-secondary-600/10 hover:text-secondary-600 hover:border-secondary-600
                  group-has-checked:bg-secondary-400/10 group-has-checked:text-secondary-600 group-has-checked:border-secondary-600
                  dark:bg-primary-400/25 dark:hover:bg-secondary-600/20 dark:hover:text-secondary-400
                  group-has-checked:dark:bg-secondary-600/20 group-has-checked:dark:text-secondary-400
                  ">
          {{ $distributor->title }}
        </div>
      </div>
    @endforeach
  </div>
</div>