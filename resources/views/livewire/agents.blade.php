<div>
    <x-card>
          <form action="{{ url('/agents/create') }}" class="flex flex-col gap-4">

            <x-form.input label="Название" inputName="title" :wire="false" />
            <x-form.input label="ИНН/КПП" inputName="inn" class="input-numeric" :wire="false" />
            <x-form.input label="ОГРН/ОГРНИП" inputName="title" class="input-numeric" :wire="false" />

            <x-form.input label="Юридический адрес" inputName="address" :wire="false" />

            <x-form.input label="ФИО" inputName="name" :wire="false" />
            <x-form.input label="Номер телефона" inputName="phone" x-mask="+7(999)999-99-99" :wire="false" />
            <x-form.input label="Email" inputName="email" :wire="false" type="email" />

            <x-button type="submit">Добавить</x-button>
          </form>
        </x-card>
</div>
