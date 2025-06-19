@extends('layout.site')

@section('content')
    <div class="px-2 sm:px-5 2xl:px-10 mb-5 2xl:mb-10">
        <h1 class="text-4xl font-semibold mb-12 py-6">Контрагенты</h1>
        <div class="grid grid-cols-[1fr] xl:grid-cols-[1fr_1fr] gap-5 2xl:gap-10">
          <div class="">side</div>
          <livewire:agents></livewire:agents>
        </div>
        <div class="pb-6"></div>
    </div>
@endsection

@push('js')
  {{-- <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
@endpush