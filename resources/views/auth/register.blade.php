<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Имя -->
        <div>
            <x-input-label for="name" :value="__('Имя')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Телефон -->
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Телефон')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" placeholder="+7 (___) ___-__-__" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Пароль -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Пароль')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Подтверждение пароля -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Подтвердите пароль')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Уже зарегистрированы?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Зарегистрироваться') }}
            </x-primary-button>
        </div>
    </form>

    <!-- Маска для телефона -->
    <script>
    document.getElementById('phone').addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');
        
        // Если начинается с 8, заменяем на 7
        if (value.startsWith('8')) {
            value = '7' + value.substring(1);
        }
        
        // Если не начинается с 7 или +7, добавляем 7
        if (!value.startsWith('7') && value.length > 0) {
            value = '7' + value;
        }
        
        // Ограничиваем до 11 цифр (7 + 9XX XXX XX XX)
        if (value.length > 11) {
            value = value.substring(0, 11);
        }
        
        let formatted = '';
        if (value.length > 0) {
            formatted = '+7';
        }
        if (value.length > 1) {
            formatted += ' (' + value.substring(1, 4);
        }
        if (value.length >= 5) {
            formatted += ') ' + value.substring(4, 7);
        }
        if (value.length >= 8) {
            formatted += '-' + value.substring(7, 9);
        }
        if (value.length >= 10) {
            formatted += '-' + value.substring(9, 11);
        }
        
        e.target.value = formatted;
    });
</script>
</x-guest-layout>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const phoneInput = document.getElementById('phone');
        
        phoneInput.addEventListener('input', function(e) {
            // Удаляем все нецифровые символы
            let digits = e.target.value.replace(/\D/g, '');
            
            // Если первая цифра 8, заменяем на 7
            if (digits.startsWith('8')) {
                digits = '7' + digits.substring(1);
            }
            
            // Если не начинается с 7, добавляем 7
            if (digits.length > 0 && !digits.startsWith('7')) {
                digits = '7' + digits;
            }
            
            // Ограничиваем до 11 цифр (7 + 10 цифр номера)
            digits = digits.substring(0, 11);
            
            // Форматируем: +7 (XXX) XXX-XX-XX
            let formatted = '';
            if (digits.length > 0) {
                formatted = '+7';
            }
            if (digits.length > 1) {
                formatted += ' (' + digits.substring(1, 4);
            }
            if (digits.length >= 5) {
                formatted += ') ' + digits.substring(4, 7);
            }
            if (digits.length >= 8) {
                formatted += '-' + digits.substring(7, 9);
            }
            if (digits.length >= 10) {
                formatted += '-' + digits.substring(9, 11);
            }
            
            e.target.value = formatted;
        });
        
        // При фокусе, если поле пустое, показываем +7
        phoneInput.addEventListener('focus', function(e) {
            if (e.target.value === '') {
                e.target.value = '+7';
            }
        });
    });
</script>
<script src="{{ asset('js/push-notification.js') }}"></script>