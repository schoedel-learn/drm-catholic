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

    <div class="min-h-screen overflow-hidden bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900">
        <!-- Background Decorations -->
        <div class="pointer-events-none fixed inset-0" aria-hidden="true">
            <!-- Primary glow -->
            <div
                class="animate-glow-pulse absolute -top-32 -right-32 h-[500px] w-[500px] rounded-full bg-indigo-500/20 blur-[100px]"
            ></div>
            <!-- Accent glow -->
            <div
                class="animate-glow-pulse absolute top-1/2 -left-32 h-[400px] w-[400px] rounded-full bg-purple-500/15 blur-[80px]"
                style="animation-delay: 1s"
            ></div>
            <!-- Tertiary glow -->
            <div
                class="animate-glow-pulse absolute right-1/4 bottom-0 h-[300px] w-[300px] rounded-full bg-blue-500/10 blur-[60px]"
                style="animation-delay: 2s"
            ></div>
            <!-- Grid pattern overlay -->
            <div
                class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:64px_64px]"
            ></div>
        </div>

        <!-- Navigation -->
        <header class="relative z-20">
            <nav class="mx-auto max-w-7xl px-6 lg:px-8" aria-label="Main navigation">
                <div class="flex h-20 items-center justify-between">
                    <!-- Logo -->
                    <a href="/" class="group flex items-center gap-3" aria-label="Catholic.Work Home">
                        <img src="/images/logo.png" alt="Catholic.Work" class="h-8 w-8" />
                        <span class="text-lg font-bold tracking-tight text-white">Catholic.Work</span>
                    </a>

                    <!-- Auth Links -->
                    <div v-if="canLogin" class="flex items-center gap-3">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="btn-primary text-sm">
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-4 py-2 text-sm font-medium text-slate-300 transition-colors duration-200 hover:text-white"
                            >
                                Sign In
                            </Link>
                            <Link v-if="canRegister" :href="route('register')" class="btn-primary text-sm">
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
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="mx-auto max-w-4xl text-center">
                        <!-- Tagline -->
                        <p
                            :class="[
                                'mb-6 text-base text-slate-400 italic transition-all duration-700 lg:text-lg',
                                isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                            ]"
                        >
                            Built for Catholic dioceses
                        </p>

                        <!-- Headline -->
                        <h1
                            :class="[
                                'mb-8 text-5xl leading-[1.1] font-extrabold tracking-tight transition-all delay-100 duration-700 sm:text-6xl lg:text-7xl',
                                isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                            ]"
                        >
                            <span class="text-white">Diocesan</span>
                            <br />
                            <span class="text-gradient">Relationship Manager</span>
                        </h1>

                        <!-- Subheadline -->
                        <p
                            :class="[
                                'mx-auto mb-12 max-w-2xl text-xl leading-relaxed text-slate-400 transition-all delay-200 duration-700 lg:text-2xl',
                                isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                            ]"
                        >
                            Nurture relationships, strengthen ministries,<br class="hidden sm:inline" />
                            grow the Kingdom.
                        </p>

                        <!-- CTA Buttons -->
                        <div
                            :class="[
                                'flex flex-col items-center justify-center gap-4 transition-all delay-300 duration-700 sm:flex-row',
                                isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                            ]"
                        >
                            <template v-if="canLogin && !$page.props.auth.user">
                                <Link
                                    :href="route('login')"
                                    class="w-full rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 px-8 py-4 text-base font-semibold text-white shadow-2xl shadow-indigo-500/25 transition-all duration-300 hover:-translate-y-0.5 hover:from-indigo-600 hover:to-purple-700 hover:shadow-indigo-500/40 sm:w-auto"
                                >
                                    Sign In to Dashboard
                                </Link>
                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="w-full rounded-xl border border-white/10 bg-white/5 px-8 py-4 text-base font-semibold text-white backdrop-blur-sm transition-all duration-300 hover:border-white/20 hover:bg-white/10 sm:w-auto"
                                >
                                    Request Access
                                </Link>
                            </template>
                            <Link
                                v-else-if="$page.props.auth.user"
                                :href="route('dashboard')"
                                class="rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 px-8 py-4 text-base font-semibold text-white shadow-2xl shadow-indigo-500/25 transition-all duration-300 hover:-translate-y-0.5 hover:from-indigo-600 hover:to-purple-700 hover:shadow-indigo-500/40"
                            >
                                Go to Dashboard →
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Trust Indicators -->
            <section class="relative border-y border-white/5 py-12">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-6 text-sm">
                        <div class="flex items-center gap-2.5 text-slate-400">
                            <svg
                                class="h-5 w-5 text-emerald-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                />
                            </svg>
                            <span>Purpose-Built for Dioceses</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-slate-400">
                            <svg
                                class="h-5 w-5 text-emerald-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                />
                            </svg>
                            <span>Secure & Private</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-slate-400">
                            <svg
                                class="h-5 w-5 text-emerald-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"
                                />
                            </svg>
                            <span>AI-Powered Insights</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Grid -->
            <section class="relative py-24 lg:py-32" aria-labelledby="features-heading">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="mb-16 text-center">
                        <h2 id="features-heading" class="mb-4 text-3xl font-bold text-white lg:text-4xl">
                            Everything you need to manage your diocese
                        </h2>
                        <p class="mx-auto max-w-2xl text-lg text-slate-400">
                            A unified platform for contacts, organizations, and communications.
                        </p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-3 lg:gap-8">
                        <!-- Contact Management -->
                        <a href="/contacts" class="group surface-glass-hover relative p-8 lg:p-10">
                            <!-- Icon -->
                            <div
                                class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/25 transition-shadow duration-300 group-hover:shadow-blue-500/40"
                            >
                                <svg
                                    class="h-7 w-7 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                    />
                                </svg>
                            </div>

                            <h3 class="mb-3 text-xl font-semibold text-white">Contact Management</h3>
                            <p class="mb-6 leading-relaxed text-slate-400">
                                Organize clergy, staff, and parishioners with custom fields and smart tagging.
                            </p>

                            <span
                                class="inline-flex items-center text-sm font-medium text-indigo-400 transition-colors group-hover:text-indigo-300"
                            >
                                Explore Contacts
                                <svg
                                    class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>
                            </span>
                        </a>

                        <!-- Organizations -->
                        <a href="/organizations" class="group surface-glass-hover relative p-8 lg:p-10">
                            <div
                                class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 shadow-lg shadow-purple-500/25 transition-shadow duration-300 group-hover:shadow-purple-500/40"
                            >
                                <svg
                                    class="h-7 w-7 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                    />
                                </svg>
                            </div>

                            <h3 class="mb-3 text-xl font-semibold text-white">Organization Directory</h3>
                            <p class="mb-6 leading-relaxed text-slate-400">
                                Manage parishes, schools, hospitals, and charities in a unified hierarchy.
                            </p>

                            <span
                                class="inline-flex items-center text-sm font-medium text-purple-400 transition-colors group-hover:text-purple-300"
                            >
                                View Organizations
                                <svg
                                    class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>
                            </span>
                        </a>

                        <!-- Forms -->
                        <a href="/form-builder" class="group surface-glass-hover relative p-8 lg:p-10">
                            <div
                                class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/25 transition-shadow duration-300 group-hover:shadow-emerald-500/40"
                            >
                                <svg
                                    class="h-7 w-7 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                            </div>

                            <h3 class="mb-3 text-xl font-semibold text-white">Smart Forms</h3>
                            <p class="mb-6 leading-relaxed text-slate-400">
                                Create shareable forms for data collection with automatic CRM integration.
                            </p>

                            <span
                                class="inline-flex items-center text-sm font-medium text-emerald-400 transition-colors group-hover:text-emerald-300"
                            >
                                Build Forms
                                <svg
                                    class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>
                            </span>
                        </a>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="relative py-24">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="surface-glass relative overflow-hidden p-12 text-center lg:p-16">
                        <!-- Inner glow -->
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 via-transparent to-purple-500/5"
                        ></div>

                        <div class="relative">
                            <h2 class="mb-4 text-3xl font-bold text-white lg:text-4xl">
                                Ready to transform your diocesan operations?
                            </h2>
                            <p class="mx-auto mb-8 max-w-2xl text-lg text-slate-400">
                                Join founding dioceses shaping the future of Catholic relationship management.
                            </p>

                            <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                                <a
                                    href="#demo"
                                    class="rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 px-8 py-4 text-base font-semibold text-white shadow-2xl shadow-indigo-500/25 transition-all duration-300 hover:-translate-y-0.5 hover:from-indigo-600 hover:to-purple-700 hover:shadow-indigo-500/40"
                                >
                                    Request a Demo
                                </a>
                                <a
                                    href="mailto:contact@drm.catholic.work"
                                    class="px-8 py-4 text-base font-semibold text-slate-300 transition-colors hover:text-white"
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
            <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                    <p class="text-sm text-slate-500">
                        © {{ new Date().getFullYear() }} DRM.Catholic.Work. Built for the Catholic community.
                    </p>
                    <div class="flex items-center gap-6 text-sm text-slate-500">
                        <a href="#" class="transition-colors hover:text-slate-300">Privacy</a>
                        <a href="#" class="transition-colors hover:text-slate-300">Terms</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
