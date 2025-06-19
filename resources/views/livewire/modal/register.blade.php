<div class="">
    <h3 class="font-bold text-xl mb-6">Регистрация</h3>
    {{-- <p class="mb-5">Ссылка для сброса пароля будет отправлена на указанный вами электронный адрес. Просим проверить входящие сообщения, включая папку «Спам», и следовать инструкциям для установки нового пароля.</p> --}}
    <form wire:submit.prevent="reg" action="{{ route('register') }}" id="register" class="flex flex-col justify-start items-stretch gap-6" method="POST">
        @csrf
        <div class="">
            <x-form.input label="ФИО" type="text" labelClass="dark:!bg-primary-800" :wire="false"
                :attrs="[
                    'required' => 'required',
                    'name' => 'name',
                    'id' => 'reg_name',
                    'wire:model.live' => 'register.name',
                ]" />
            @error('name')
                <div class="text-red-500 mt-2 inline-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="">
            <x-form.input label="E-mail" type="email" labelClass="dark:!bg-primary-800" :wire="false"
                :attrs="[
                    'required' => 'required',
                    'name' => 'email',
                    'id' => 'reg_email',
                    'wire:model.live' => 'register.email',
                  ]" 
                />
            @error('email')
                <div class="text-red-500 mt-2 inline-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="">
            <x-form.input label="Номер телефона" type="text" :wire="false"
                labelClass="dark:!bg-primary-800" placeholder="+7..." :attrs="[
                    'autocomplete' => 'false',
                    'id' => 'user-phone',
                    'name' => 'phone',
                    'autocomplete' => 'new-password',
                    'wire:model.live' => 'register.phone',
                ]" x-mask="+7(999)999-99-99" />

            @error('phone')
                <div class="text-red-500 mt-2 inline-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="">
            <x-form.input label="Пароль" type="password" labelClass="dark:!bg-primary-800" :wire="false"
                :attrs="[
                    'type' => 'password',
                    'required' => 'required',
                    'name' => 'password',
                    'id' => 'reg_password',
                    'autocomplete' => 'new-password',
                    'wire:model.live' => 'register.password',
                ]" />

            @error('password')
                <div class="text-red-500 mt-2 inline-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="">
            <x-form.input label="Повторите пароль" type="password" labelClass="dark:!bg-primary-800" :wire="false"
                :attrs="[
                    'type' => 'password',
                    'required' => 'required',
                    'name' => 'password_confirm',
                    'id' => 'reg_password_confirm',
                    'autocomplete' => 'new-password',
                    'wire:model.live' => 'register.password_confirm',
                ]" />

            @error('password_confirm')
                <div class="text-red-500 mt-2 inline-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex justify-between items-stretch gap-2">
            <x-button wire:click.prevent="openAuthModal" outlined class="w-full">Назад</x-button>
            <x-button class="w-full">Зарегистрироваться</x-button>
        </div>
    </form>
</div>