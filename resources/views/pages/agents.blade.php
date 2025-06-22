@extends('layout.site')

@section('content')
    <div class="px-2 sm:px-5 2xl:px-10 mb-5 2xl:mb-10">
        <h1 class="text-4xl font-semibold mb-12 py-6">Контрагенты</h1>
        <livewire:agents></livewire:agents>
        <div class="pb-6"></div>
    </div>
@endsection

@push('js')
  
@endpush