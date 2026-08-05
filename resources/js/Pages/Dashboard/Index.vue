<template>
    <DashboardLayout active-tab="dashboard">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold mb-6" style="color: #1F4234;">Личная информация</h1>
            
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: #68736B;">Имя</label>
                        <input 
                            type="text" 
                            :value="user.name" 
                            disabled
                            class="w-full px-4 py-3 rounded-xl border-2 bg-gray-50 focus:outline-none"
                            style="border-color: #E8E3DA;"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: #68736B;">Email</label>
                        <input 
                            type="email" 
                            :value="user.email" 
                            disabled
                            class="w-full px-4 py-3 rounded-xl border-2 bg-gray-50 focus:outline-none"
                            style="border-color: #E8E3DA;"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: #68736B;">Телефон</label>
                        <input 
                            type="tel" 
                            :value="user.phone || 'Не указан'" 
                            disabled
                            class="w-full px-4 py-3 rounded-xl border-2 bg-gray-50 focus:outline-none"
                            style="border-color: #E8E3DA;"
                        >
                    </div>
                    
                    <div class="pt-4">
                        <Link 
                            href="/profile/edit"
                            class="inline-block px-6 py-3 rounded-xl text-white font-medium transition-all hover:shadow-lg"
                            style="background-color: #315C47;"
                        >
                            Редактировать профиль
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;
</script>