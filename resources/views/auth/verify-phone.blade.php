<x-guest-layout>
<div class="mb-4 text-sm text-gray-600">
    @php
        $phone = Auth::user()->phone;
        $formattedPhone = preg_replace('/(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})/', '+$1 ($2) $3-$4-$5', preg_replace('/\D/', '', $phone));
    @endphp
    {{ __('Мы отправили 4-значный код на номер') }} <strong>{{ $formattedPhone }}</strong>.
    {{ __('Введите код для подтверждения.') }}
</div>

    @if (session('status') === 'code-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('Новый код отправлен.') }}
        </div>
    @endif

    <form method="POST" action="{{ route('phone.verify') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Код подтверждения')" />
            <x-text-input id="code" class="block mt-1 w-full text-center text-2xl tracking-widest" type="text" name="code" required autofocus maxlength="4" pattern="\d{4}" inputmode="numeric" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <form method="POST" action="{{ route('phone.resend') }}">
                @csrf
                <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900">
                    {{ __('Отправить код повторно') }}
                </button>
            </form>

            <x-primary-button>
                {{ __('Подтвердить') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>