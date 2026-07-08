@extends('admin.layout')

@section('title', 'Создать пользователя')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Создать пользователя</h1>
        <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:underline">← Назад к списку</a>
    </div>

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" class="bg-white p-6 rounded-lg shadow max-w-2xl">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Имя *</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Email *</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Телефон *</label>
            <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="+7 (___) ___-__-__"
                   class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Пароль *</label>
            <input type="password" name="password" required
                   class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-2">Роль</label>
            <select name="role" class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>Пользователь</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Администратор</option>
            </select>
        </div>

        <button type="submit" class="btn-gradient px-6 py-2 rounded-lg text-white">
            Создать пользователя
        </button>
    </form>
</div>
@endsection
