<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

defineProps({
    title: String,
});

const page = usePage();
const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);

// Navigation items
const navigation = [
    { 
        name: 'Dashboard', 
        href: 'dashboard', 
        icon: 'home',
        current: () => route().current('dashboard')
    },
    { 
        name: 'Contacts', 
        href: 'contacts.index', 
        icon: 'users',
        current: () => route().current('contacts.*')
    },
    { 
        name: 'Organizations', 
        href: 'organizations.index', 
        icon: 'building',
        current: () => route().current('organizations.*')
    },
    { 
        name: 'Forms', 
        href: 'form-builder.index', 
        icon: 'document',
        current: () => route().current('form-builder.*')
    },
];

const secondaryNav = [
    { 
        name: 'Custom Fields', 
        href: 'custom-fields.index', 
        icon: 'cog',
        current: () => route().current('custom-fields.*')
    },
];

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};

const currentTeamName = computed(() => {
    return page.props.auth?.user?.current_team?.name || 'Team';
});
</script>

<template>
    <div>
        <Head :title="title" />
        <Banner />

        <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950">
            <!-- Mobile sidebar backdrop -->
            <div 
                v-if="sidebarOpen" 
                class="fixed inset-0 z-40 bg-slate-950/80 backdrop-blur-sm lg:hidden"
                @click="sidebarOpen = false"
            ></div>

            <!-- Sidebar -->
            <aside 
                :class="[
                    'fixed inset-y-0 left-0 z-50 flex flex-col bg-slate-900/95 backdrop-blur-xl border-r border-white/10 transition-all duration-300',
                    sidebarCollapsed ? 'w-20' : 'w-64',
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
                ]"
            >
                <!-- Logo -->
                <div class="flex items-center justify-between h-16 px-4 border-b border-white/10">
                    <Link 
                        :href="route('dashboard')" 
                        :class="['flex items-center gap-3', sidebarCollapsed ? 'justify-center w-full' : '']"
                    >
                        <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/30">
                            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-4H7v-2h4V7h2v4h4v2h-4v4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <span v-if="!sidebarCollapsed" class="text-lg font-bold text-white">DRM</span>
                    </Link>
                    
                    <!-- Collapse button (desktop) -->
                    <button 
                        @click="sidebarCollapsed = !sidebarCollapsed"
                        class="hidden lg:flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors"
                    >
                        <svg :class="['w-5 h-5 transition-transform', sidebarCollapsed ? 'rotate-180' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        </svg>
                    </button>
                    
                    <!-- Close button (mobile) -->
                    <button 
                        @click="sidebarOpen = false"
                        class="lg:hidden flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-white hover:bg-white/10"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    <!-- Main Navigation -->
                    <template v-for="item in navigation" :key="item.name">
                        <Link
                            :href="route(item.href)"
                            :class="[
                                'group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200',
                                item.current() 
                                    ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30' 
                                    : 'text-slate-400 hover:text-white hover:bg-white/10',
                                sidebarCollapsed ? 'justify-center' : ''
                            ]"
                        >
                            <!-- Icons -->
                            <svg v-if="item.icon === 'home'" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <svg v-else-if="item.icon === 'users'" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <svg v-else-if="item.icon === 'building'" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <svg v-else-if="item.icon === 'document'" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span v-if="!sidebarCollapsed">{{ item.name }}</span>
                        </Link>
                    </template>

                    <!-- Divider -->
                    <div class="my-4 border-t border-white/10"></div>

                    <!-- Secondary Navigation -->
                    <div v-if="!sidebarCollapsed" class="px-3 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        Settings
                    </div>
                    <template v-for="item in secondaryNav" :key="item.name">
                        <Link
                            :href="route(item.href)"
                            :class="[
                                'group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200',
                                item.current() 
                                    ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30' 
                                    : 'text-slate-400 hover:text-white hover:bg-white/10',
                                sidebarCollapsed ? 'justify-center' : ''
                            ]"
                        >
                            <svg v-if="item.icon === 'cog'" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span v-if="!sidebarCollapsed">{{ item.name }}</span>
                        </Link>
                    </template>
                </nav>

                <!-- Team & User -->
                <div class="border-t border-white/10 p-4">
                    <!-- Team Dropdown -->
                    <Dropdown 
                        v-if="$page.props.jetstream?.hasTeamFeatures && !sidebarCollapsed" 
                        align="right" 
                        width="48"
                    >
                        <template #trigger>
                            <button class="flex items-center gap-3 w-full px-3 py-2 rounded-xl text-left text-sm text-slate-300 hover:bg-white/10 transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white text-xs font-bold">
                                    {{ currentTeamName.charAt(0) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-white truncate">{{ currentTeamName }}</div>
                                    <div class="text-xs text-slate-500">Diocese</div>
                                </div>
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                </svg>
                            </button>
                        </template>
                        <template #content>
                            <div class="py-1">
                                <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)">
                                    Team Settings
                                </DropdownLink>
                                <DropdownLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')">
                                    Create New Team
                                </DropdownLink>
                            </div>
                            <template v-if="$page.props.auth.user.all_teams.length > 1">
                                <div class="border-t border-gray-200 my-1"></div>
                                <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                    <form @submit.prevent="switchToTeam(team)">
                                        <DropdownLink as="button">
                                            <div class="flex items-center">
                                                <svg v-if="team.id == $page.props.auth.user.current_team_id" class="mr-2 h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ team.name }}
                                            </div>
                                        </DropdownLink>
                                    </form>
                                </template>
                            </template>
                        </template>
                    </Dropdown>

                    <!-- User Menu -->
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button :class="['flex items-center gap-3 w-full px-3 py-2 rounded-xl text-left text-sm text-slate-300 hover:bg-white/10 transition-colors', sidebarCollapsed ? 'justify-center' : '']">
                                <div v-if="$page.props.jetstream?.managesProfilePhotos" class="shrink-0">
                                    <img class="w-8 h-8 rounded-full object-cover ring-2 ring-white/10" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                </div>
                                <div v-else class="w-8 h-8 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 text-sm font-semibold shrink-0">
                                    {{ $page.props.auth.user.name.charAt(0) }}
                                </div>
                                <div v-if="!sidebarCollapsed" class="flex-1 min-w-0">
                                    <div class="font-medium text-white truncate">{{ $page.props.auth.user.name }}</div>
                                    <div class="text-xs text-slate-500 truncate">{{ $page.props.auth.user.email }}</div>
                                </div>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.show')">Profile</DropdownLink>
                            <DropdownLink v-if="$page.props.jetstream?.hasApiFeatures" :href="route('api-tokens.index')">API Tokens</DropdownLink>
                            <div class="border-t border-gray-200 my-1"></div>
                            <form @submit.prevent="logout">
                                <DropdownLink as="button">Log Out</DropdownLink>
                            </form>
                        </template>
                    </Dropdown>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div :class="['transition-all duration-300', sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64']">
                <!-- Top Bar -->
                <header class="sticky top-0 z-30 flex items-center h-16 px-4 sm:px-6 lg:px-8 bg-slate-900/80 backdrop-blur-xl border-b border-white/10">
                    <!-- Mobile menu button -->
                    <button
                        @click="sidebarOpen = true"
                        class="lg:hidden flex items-center justify-center w-10 h-10 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 transition-colors mr-4"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Page Title -->
                    <div class="flex-1">
                        <slot name="header">
                            <h1 class="text-lg font-semibold text-white">{{ title }}</h1>
                        </slot>
                    </div>

                    <!-- Quick Actions -->
                    <div class="flex items-center gap-3">
                        <slot name="actions" />
                    </div>
                </header>

                <!-- Page Content -->
                <main class="p-4 sm:p-6 lg:p-8">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
