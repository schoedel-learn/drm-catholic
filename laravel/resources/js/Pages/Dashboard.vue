<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Quick Actions Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Contacts Card -->
                    <Link :href="route('contacts.index')" class="group bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 hover:shadow-lg transition-all duration-200 border border-transparent hover:border-indigo-200">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">Contacts</h3>
                                <p class="text-sm text-gray-500">Manage people</p>
                            </div>
                        </div>
                    </Link>

                    <!-- Organizations Card -->
                    <Link :href="route('organizations.index')" class="group bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 hover:shadow-lg transition-all duration-200 border border-transparent hover:border-purple-200">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 group-hover:text-purple-600 transition-colors">Organizations</h3>
                                <p class="text-sm text-gray-500">Parishes & more</p>
                            </div>
                        </div>
                    </Link>

                    <!-- Form Builder Card -->
                    <Link :href="route('form-builder.index')" class="group bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 hover:shadow-lg transition-all duration-200 border border-transparent hover:border-green-200">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 group-hover:text-green-600 transition-colors">Form Builder</h3>
                                <p class="text-sm text-gray-500">Public forms</p>
                            </div>
                        </div>
                    </Link>

                    <!-- Settings Card -->
                    <Link :href="route('custom-fields.index')" class="group bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 hover:shadow-lg transition-all duration-200 border border-transparent hover:border-amber-200">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 group-hover:text-amber-600 transition-colors">Custom Fields</h3>
                                <p class="text-sm text-gray-500">Configure data</p>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Welcome Message -->
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 overflow-hidden shadow-xl sm:rounded-xl p-8 text-white">
                    <h2 class="text-2xl font-bold mb-2">Welcome to your Diocese CRM</h2>
                    <p class="text-indigo-100">Manage contacts, organizations, and collect data with custom forms.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
