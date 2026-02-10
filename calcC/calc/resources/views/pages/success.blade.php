@extends('layout.site')

@section('content')
    <div class="px-2 sm:px-5 2xl:px-10 mb-5 2xl:mb-10">
        {{-- <h1 class="text-4xl font-semibold mb-12 py-6">История заказов</h1> --}}
        <div class="py-4">
          <livewire:success :order="$order"></livewire:success>
        </div>
    </div>
@endsection