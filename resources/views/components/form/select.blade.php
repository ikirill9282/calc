<select 
  name="{{ $name ?? '' }}" 
  id="{{ $id ?? '' }}"
  class="ring-0 border border-primary-600/50 rounded-lg {{ $class ?? '' }}"
  >
  <option value="" selected disabled></option>
  <option value="1">Склад Екатеринбург</option>
  <option value="2">Склад Иваново</option>
  <option value="3">Склад Казань</option>
  <option value="4">Склад Краснодар</option>
  <option value="5">Склад Москва</option>
  <option value="6">Склад Ростов-на-Дону</option>
  <option value="7">Склад Санкт-Петербург</option>
</select>