<script setup>
import { ref, computed, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';

const page = usePage();
const { isAdmin } = usePermissions();
const sidebarOpen = ref(true);

const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);

const showFlash = ref(false);
let flashTimer;

function triggerFlash() {
    if (flash.value?.success) {
        showFlash.value = true;
        clearTimeout(flashTimer);
        flashTimer = setTimeout(() => { showFlash.value = false; }, 3500);
    }
}

// Fire on initial page load
triggerFlash();

// Fire on every Inertia navigation — even when the flash message string is identical
const stopFlashListener = router.on('navigate', triggerFlash);
onUnmounted(() => stopFlashListener());

const initials = computed(() => {
    const parts = (user.value?.name || 'U').split(' ');
    return parts.map(p => p[0]).join('').toUpperCase().slice(0, 2);
});

const navItems = computed(() => [
    {
        name: 'Dashboard',
        href: route('dashboard'),
        icon: '◻',
        active: route().current('dashboard'),
        show: true,
    },
    {
        name: 'Users',
        href: route('users.index'),
        icon: '◻',
        active: route().current('users.*'),
        show: isAdmin.value,
    },
    {
        name: 'Billing',
        href: route('billing.plans'),
        icon: '◻',
        active: route().current('billing.*'),
        show: isAdmin.value,
    },
    {
        name: 'Settings',
        href: route('profile.edit'),
        icon: '◻',
        active: route().current('profile.*'),
        show: true,
    },
]);

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <div class="flex h-screen bg-gray-50 overflow-hidden">
        <!-- Sidebar -->
        <aside
            :class="sidebarOpen ? 'w-64' : 'w-16'"
            class="flex flex-col bg-white border-r border-gray-200 transition-all duration-200 shrink-0"
        >
            <!-- Logo -->
            <div class="flex h-16 items-center px-4 border-b border-gray-200">
                <div v-if="sidebarOpen" class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-lg bg-indigo-600 flex items-center justify-center">
                        <span class="text-white text-xs font-bold">S</span>
                    </div>
                    <span class="font-semibold text-gray-900">SaaS Demo</span>
                </div>
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="ml-auto p-1 rounded text-gray-400 hover:text-gray-600"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                <template v-for="item in navItems" :key="item.name">
                    <Link
                        v-if="item.show"
                        :href="item.href"
                        :class="[
                            item.active
                                ? 'bg-indigo-50 text-indigo-700 font-semibold'
                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors'
                        ]"
                    >
                        <span class="shrink-0 text-lg leading-none">
                            <svg v-if="item.name === 'Dashboard'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <svg v-else-if="item.name === 'Users'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>
                            </svg>
                            <svg v-else-if="item.name === 'Billing'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </span>
                        <span v-if="sidebarOpen">{{ item.name }}</span>
                    </Link>
                </template>
            </nav>

            <!-- User footer -->
            <div class="p-3 border-t border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                        <span class="text-indigo-700 text-xs font-bold">{{ initials }}</span>
                    </div>
                    <div v-if="sidebarOpen" class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ user?.name }}</p>
                        <p class="text-xs text-gray-500 truncate capitalize">{{ user?.roles?.[0] ?? 'member' }}</p>
                    </div>
                    <button
                        v-if="sidebarOpen"
                        @click="logout"
                        class="p-1 text-gray-400 hover:text-gray-600 rounded"
                        title="Logout"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top bar -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center px-6 shrink-0">
                <slot name="header">
                    <h1 class="text-lg font-semibold text-gray-900">Dashboard</h1>
                </slot>
                <!-- Flash messages -->
                <div class="ml-auto">
                    <transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 translate-y-1"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 translate-y-1"
                    >
                        <div
                            v-if="showFlash && flash?.success"
                            class="bg-emerald-50 text-emerald-700 text-sm px-4 py-2 rounded-lg border border-emerald-200"
                        >
                            {{ flash.success }}
                        </div>
                    </transition>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
