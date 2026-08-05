@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-[#315C47] focus:ring-[#315C47] rounded-md shadow-sm']) !!}>
