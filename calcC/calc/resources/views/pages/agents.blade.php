@extends('layout.site')

@section('content')
    <div class="px-2 sm:px-5 2xl:px-10 mb-5 2xl:mb-10">
        <h1 class="text-4xl font-semibold mb-12 py-6">Контрагенты</h1>
        <livewire:agents></livewire:agents>
        <div class="pb-6"></div>
    </div>
@endsection

@push('js')
<script>
  document.addEventListener('livewire:load', () => {
    const isTitleInput = (element) => element instanceof HTMLInputElement && element.name === 'title';

    document.addEventListener('keydown', (event) => {
      if (!isTitleInput(event.target)) {
        return;
      }

      const allowedKeys = ['Backspace', 'Tab', 'ArrowLeft', 'ArrowRight', 'Delete'];
      if (allowedKeys.includes(event.key)) {
        return;
      }

      if (!/\d/.test(event.key)) {
        event.preventDefault();
      }
    });

    document.addEventListener('paste', (event) => {
      if (!isTitleInput(event.target)) {
        return;
      }

      event.preventDefault();
      const digits = (event.clipboardData.getData('text') ?? '').replace(/\D+/g, '');
      const input = event.target;
      const { selectionStart, selectionEnd, value } = input;
      const nextValue = value.slice(0, selectionStart) + digits + value.slice(selectionEnd);

      input.value = nextValue;
      input.dispatchEvent(new Event('input', { bubbles: true }));
    });
  });
</script>
@endpush