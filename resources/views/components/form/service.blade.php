<div class="flex gap-4">
  <input type="hidden" name="distributor">
  <div class="py-4">{{ $label ?? 'Куда:' }}</div>
  <div class="flex flex-wrap justify-start items-start gap-3">
    @foreach (\App\Models\Distributor::all() as $distributor)
      <div class="group">
        <div class="px-6 py-4 border rounded-xl hover:cursor-pointer transition
                  border-primary-700/10 bg-primary-700/10 hover:bg-secondary-600/10 hover:text-secondary-600 hover:border-secondary-600
                  group-[.active]:bg-secondary-400/10 group-[.active]:text-secondary-600 group-[.active]:border-secondary-600
                  dark:bg-primary-400/25 dark:hover:bg-secondary-600/20 dark:hover:text-secondary-400
                  group-[.active]:dark:bg-secondary-600/20 group-[.active]:dark:text-secondary-400
                  ">
          {{ $distributor->title }}
        </div>
      </div>
    @endforeach
  </div>
</div>