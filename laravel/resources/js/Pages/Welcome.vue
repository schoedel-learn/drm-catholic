<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

// Staggered animation tracking
const isVisible = ref(false);
onMounted(() => {
    requestAnimationFrame(() => {
        isVisible.value = true;
    });
});
</script>

<template>
    <Head title="DRM.Catholic.Work - Diocesan Relationship Manager" />
    
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900 overflow-hidden">
        <!-- Background Decorations -->
        <div class="fixed inset-0 pointer-events-none" aria-hidden="true">
            <!-- Primary glow -->
            <div class="absolute -top-32 -right-32 w-[500px] h-[500px] bg-indigo-500/20 rounded-full blur-[100px] animate-glow-pulse"></div>
            <!-- Accent glow -->
            <div class="absolute top-1/2 -left-32 w-[400px] h-[400px] bg-purple-500/15 rounded-full blur-[80px] animate-glow-pulse" style="animation-delay: 1s;"></div>
            <!-- Tertiary glow -->
            <div class="absolute bottom-0 right-1/4 w-[300px] h-[300px] bg-blue-500/10 rounded-full blur-[60px] animate-glow-pulse" style="animation-delay: 2s;"></div>
            <!-- Grid pattern overlay -->
            <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:64px_64px]"></div>
        </div>

        <!-- Navigation -->
        <header class="relative z-20">
            <nav class="max-w-7xl mx-auto px-6 lg:px-8" aria-label="Main navigation">
                <div class="flex items-center justify-between h-20">
                    <!-- Logo -->
                    <a href="/" class="group flex items-center gap-3" aria-label="Catholic.Work Home">
                        <img src="/images/logo.png" alt="Catholic.Work" class="w-8 h-8" />
                        <span class="text-lg font-bold text-white tracking-tight">Catholic.Work</span>
                    </a>
                    
                    <!-- Auth Links -->
                    <div v-if="canLogin" class="flex items-center gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="btn-primary text-sm"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white transition-colors duration-200"
                            >
                                Sign In
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="btn-primary text-sm"
                            >
                                Get Started
                            </Link>
                        </template>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="relative z-10">
            <!-- Hero Section -->
            <section class="relative pt-16 pb-24 lg:pt-24 lg:pb-32">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    <div class="max-w-4xl mx-auto text-center">
                        <!-- Tagline -->
                        <p 
                            :class="['text-base lg:text-lg italic text-slate-400 mb-6 transition-all duration-700', isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4']"
                        >
                            Built for Catholic dioceses
                        </p>
                        
                        <!-- Headline -->
                        <h1 
                            :class="['text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.1] mb-8 transition-all duration-700 delay-100', isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4']"
                        >
                            <span class="text-white">Diocesan</span>
                            <br />
                            <span class="text-gradient">Relationship Manager</span>
                        </h1>
                        
                        <!-- Subheadline -->
                        <p 
                            :class="['text-xl lg:text-2xl text-slate-400 max-w-2xl mx-auto mb-12 leading-relaxed transition-all duration-700 delay-200', isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4']"
                        >
                            Nurture relationships, strengthen ministries,<br class="hidden sm:inline" />
                            grow the Kingdom.
                        </p>
                        
                        <!-- CTA Buttons -->
                        <div 
                            :class="['flex flex-col sm:flex-row items-center justify-center gap-4 transition-all duration-700 delay-300', isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4']"
                        >
                            <template v-if="canLogin && !$page.props.auth.user">
                                <Link
                                    :href="route('login')"
                                    class="w-full sm:w-auto px-8 py-4 text-base font-semibold text-white bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl hover:from-indigo-600 hover:to-purple-700 shadow-2xl shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all duration-300 hover:-translate-y-0.5"
                                >
                                    Sign In to Dashboard
                                </Link>
                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="w-full sm:w-auto px-8 py-4 text-base font-semibold text-white bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 hover:bg-white/10 hover:border-white/20 transition-all duration-300"
                                >
                                    Request Access
                                </Link>
                            </template>
                            <Link
                                v-else-if="$page.props.auth.user"
                                :href="route('dashboard')"
                                class="px-8 py-4 text-base font-semibold text-white bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl hover:from-indigo-600 hover:to-purple-700 shadow-2xl shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all duration-300 hover:-translate-y-0.5"
                            >
                                Go to Dashboard →
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Trust Indicators -->
            <section class="relative py-12 border-y border-white/5">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-6 text-sm">
                        <div class="flex items-center gap-2.5 text-slate-400">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span>Purpose-Built for Dioceses</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-slate-400">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>Secure & Private</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-slate-400">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>AI-Powered Insights</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Grid -->
            <section class="relative py-24 lg:py-32" aria-labelledby="features-heading">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    <div class="text-center mb-16">
                        <h2 id="features-heading" class="text-3xl lg:text-4xl font-bold text-white mb-4">
                            Everything you need to manage your diocese
                        </h2>
                        <p class="text-lg text-slate-400 max-w-2xl mx-auto">
                            A unified platform for contacts, organizations, and communications.
                        </p>
                    </div>
                    
                    <div class="grid md:grid-cols-3 gap-6 lg:gap-8">
                        <!-- Contact Management -->
                        <a 
                            href="/contacts" 
                            class="group relative surface-glass-hover p-8 lg:p-10"
                        >
                            <!-- Icon -->
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center mb-6 shadow-lg shadow-blue-500/25 group-hover:shadow-blue-500/40 transition-shadow duration-300">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            
                            <h3 class="text-xl font-semibold text-white mb-3">
                                Contact Management
                            </h3>
                            <p class="text-slate-400 leading-relaxed mb-6">
                                Organize clergy, staff, and parishioners with custom fields and smart tagging.
                            </p>
                            
                            <span class="inline-flex items-center text-indigo-400 text-sm font-medium group-hover:text-indigo-300 transition-colors">
                                Explore Contacts
                                <svg class="ml-2 w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </a>

                        <!-- Organizations -->
                        <a 
                            href="/organizations" 
                            class="group relative surface-glass-hover p-8 lg:p-10"
                        >
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center mb-6 shadow-lg shadow-purple-500/25 group-hover:shadow-purple-500/40 transition-shadow duration-300">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            
                            <h3 class="text-xl font-semibold text-white mb-3">
                                Organization Directory
                            </h3>
                            <p class="text-slate-400 leading-relaxed mb-6">
                                Manage parishes, schools, hospitals, and charities in a unified hierarchy.
                            </p>
                            
                            <span class="inline-flex items-center text-purple-400 text-sm font-medium group-hover:text-purple-300 transition-colors">
                                View Organizations
                                <svg class="ml-2 w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </a>

                        <!-- Forms -->
                        <a 
                            href="/form-builder" 
                            class="group relative surface-glass-hover p-8 lg:p-10"
                        >
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center mb-6 shadow-lg shadow-emerald-500/25 group-hover:shadow-emerald-500/40 transition-shadow duration-300">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            
                            <h3 class="text-xl font-semibold text-white mb-3">
                                Smart Forms
                            </h3>
                            <p class="text-slate-400 leading-relaxed mb-6">
                                Create shareable forms for data collection with automatic CRM integration.
                            </p>
                            
                            <span class="inline-flex items-center text-emerald-400 text-sm font-medium group-hover:text-emerald-300 transition-colors">
                                Build Forms
                                <svg class="ml-2 w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </a>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="relative py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    <div class="relative surface-glass p-12 lg:p-16 text-center overflow-hidden">
                        <!-- Inner glow -->
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 via-transparent to-purple-500/5"></div>
                        
                        <div class="relative">
                            <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4">
                                Ready to transform your diocesan operations?
                            </h2>
                            <p class="text-lg text-slate-400 max-w-2xl mx-auto mb-8">
                                Join founding dioceses shaping the future of Catholic relationship management.
                            </p>
                            
                            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                                <a 
                                    href="#demo"
                                    class="px-8 py-4 text-base font-semibold text-white bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl hover:from-indigo-600 hover:to-purple-700 shadow-2xl shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all duration-300 hover:-translate-y-0.5"
                                >
                                    Request a Demo
                                </a>
                                <a 
                                    href="mailto:contact@drm.catholic.work"
                                    class="px-8 py-4 text-base font-semibold text-slate-300 hover:text-white transition-colors"
                                >
                                    Contact Us →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 border-t border-white/5">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-12">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-slate-500">
                        © {{ new Date().getFullYear() }} DRM.Catholic.Work. Built for the Catholic community.
                    </p>
                    <div class="flex items-center gap-6 text-sm text-slate-500">
                        <a href="#" class="hover:text-slate-300 transition-colors">Privacy</a>
                        <a href="#" class="hover:text-slate-300 transition-colors">Terms</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
