<div class="bg-inherit">
    <h3 class="font-bold text-xl mb-6">Регистрация</h3>
    {{-- <p class="mb-5">Ссылка для сброса пароля будет отправлена на указанный вами электронный адрес. Просим проверить входящие сообщения, включая папку «Спам», и следовать инструкциям для установки нового пароля.</p> --}}
    <form wire:submit.prevent="reg" action="{{ route('register') }}" id="register" class="flex flex-col justify-start items-stretch gap-6 bg-inherit" method="POST">
        @csrf
        <div class="bg-inherit">
            <x-form.wrap label="ФИО" name="name">
              <x-form.input  
                type="text"
                name="name"
                id="reg_name"
                wire:model.live="register.name"
                autocomplete="off"
                aria-autocomplete="off"
              />
            </x-form.wrap>
        </div>
        <div class="bg-inherit">

          <x-form.wrap label="E-mail" name="email" >
            <x-form.input 
                type="email" 
                name="email"
                id="reg_email"
                wire:model.live="register.email"
                placeholder="email@example.com"
              />
          </x-form.wrap>
        </div>
        <div class="bg-inherit">
            <x-form.wrap label="Номер телефона" name="phone" >
              <x-form.input 
                wire:model="register.phone" 
                name="phone"
                x-data
                x-mask="+7(999)999-99-99" 
                x-init="elem => console.log(elem)"
                x-bind:placeholder="'+7(999)999-99-99'"
                x-init="
                  setTimeout(() => {
                    setTimeout(() => $el.dispatchEvent(new Event('focus')), 50)
                  }, 50)
                "
                />
            </x-form.wrap>
        </div>
        <div class="bg-inherit">
            <x-form.wrap label="Пароль" :show_password="true" name="password" >
              <x-form.input 
                  type="{{ array_key_exists('password', $this->showPassword) ? 'text' : 'password' }}" 
                  required
                  aria-autocomplete="off"
                  autocomplete="new-password"
                  wire:model.live="register.password"
                  name="password"
                  id="password"
                />
            </x-form.wrap>
        </div>
        <div class="bg-inherit">
          <x-form.wrap label="Повторите пароль" :show_password="true" name="password_confirm" >
            <x-form.input 
                required="required"
                type="{{ array_key_exists('password_confirm', $this->showPassword) ? 'text' : 'password' }}" 
                name='password_confirm'
                id='reg_password_confirm'
                autocomplete='new-password'
                wire:model.live='register.password_confirm'
              />
          </x-form.wrap>
        </div>

        <div class="flex justify-between items-stretch gap-2">
            <x-button wire:click.prevent="openAuthModal" outlined class="w-full">Назад</x-button>
            <x-button class="w-full">Зарегистрироваться</x-button>
        </div>
    </form>
</div>