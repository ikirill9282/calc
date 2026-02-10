@extends('layout.site')

@section('content')
    <div class="px-2 sm:px-5 2xl:px-10 mb-5 2xl:mb-10">
        <h1 class="text-4xl font-semibold mb-12 py-6">История заказов</h1>
        <livewire:history :orders="$orders"></livewire:history>
        {{-- <div class="grid grid-cols-[1fr] xl:grid-cols-[1fr_350px] 2xl:grid-cols-[1fr_400px] gap-5 2xl:gap-10">
            
        </div> --}}
    </div>
@endsection