<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { usePermissions } from '@/composables/usePermissions';

const props = defineProps({
    users:  Object,
    search: String,
});

const page = usePage();
const { isAdmin } = usePermissions();
const currentUserId = computed(() => page.props.auth.user.id);

// Search
const searchInput = ref(props.search ?? '');
let searchTimer;
watch(searchInput, (val) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(route('users.index'), { search: val }, { preserveState: true, replace: true });
    }, 300);
});

// Invite user form
const showInviteModal = ref(false);
const inviteForm = useForm({ name: '', email: '' });

function invite() {
    inviteForm.post(route('users.store'), {
        onSuccess: () => { showInviteModal.value = false; inviteForm.reset(); }
    });
}

// Role update
function updateRole(userId, role) {
    router.put(route('users.role', userId), { role }, { preserveScroll: true });
}

// Deactivate
function deactivate(userId) {
    if (confirm('Deactivate this user?')) {
        router.delete(route('users.destroy', userId), { preserveScroll: true });
    }
}

// Reactivate
function reactivate(userId) {
    if (confirm('Reactivate this user?')) {
        router.put(route('users.reactivate', userId), {}, { preserveScroll: true });
    }
}

const statusColor = (status) => status === 'active'
    ? 'bg-emerald-50 text-emerald-700'
    : 'bg-gray-100 text-gray-500';

const roleColor = (role) => role === 'admin'
    ? 'bg-indigo-50 text-indigo-700'
    : 'bg-blue-50 text-blue-700';
</script>

<template>
    <Head title="Users" />
    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Team Members</h1>
        </template>

        <!-- Toolbar -->
        <div class="flex items-center gap-4 mb-6">
            <div class="relative flex-1 max-w-sm">
                <input
                    v-model="searchInput"
                    type="text"
                    placeholder="Search users..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <button
                v-if="isAdmin"
                @click="showInviteModal = true"
                class="ml-auto bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors"
            >
                + Invite User
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th v-if="isAdmin" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 min-w-[200px]">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                                        <span class="text-indigo-700 text-xs font-bold">
                                            {{ user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0,2) }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 min-w-[130px]">
                                <!-- Editable role dropdown — hidden for the current admin (self) -->
                                <select
                                    v-if="isAdmin && user.id !== currentUserId"
                                    :value="user.roles[0]?.name"
                                    @change="updateRole(user.id, $event.target.value)"
                                    class="text-xs font-semibold rounded-full px-3 py-1 border-0 cursor-pointer appearance-none pr-6 bg-no-repeat focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                    :class="user.roles[0]?.name === 'admin' ? 'bg-indigo-50 text-indigo-700' : 'bg-blue-50 text-blue-700'"
                                    :style="{
                                        backgroundImage: user.roles[0]?.name === 'admin'
                                            ? `url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%234338ca' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\")`
                                            : `url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%231d4ed8' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\")`,
                                        backgroundPosition: 'right 8px center',
                                        backgroundSize: '10px'
                                    }"
                                >
                                    <option value="admin">Admin</option>
                                    <option value="member">Member</option>
                                </select>
                                <!-- Read-only badge for self or non-admins -->
                                <span
                                    v-else
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                    :class="roleColor(user.roles[0]?.name)"
                                >
                                    {{ user.roles[0]?.name ?? 'member' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize" :class="statusColor(user.status)">
                                    {{ user.status }}
                                </span>
                            </td>
                            <td v-if="isAdmin" class="px-6 py-4 text-right whitespace-nowrap">
                                <template v-if="user.id !== currentUserId">
                                    <button
                                        v-if="user.status === 'active'"
                                        @click="deactivate(user.id)"
                                        class="text-xs text-red-600 hover:text-red-800 font-medium"
                                    >
                                        Deactivate
                                    </button>
                                    <button
                                        v-else
                                        @click="reactivate(user.id)"
                                        class="text-xs text-emerald-600 hover:text-emerald-800 font-medium"
                                    >
                                        Reactivate
                                    </button>
                                </template>
                                <span v-else class="text-xs text-gray-400 italic">You</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
                <span>Showing {{ users.from }}–{{ users.to }} of {{ users.total }}</span>
                <div class="flex gap-2">
                    <Link
                        v-if="users.prev_page_url"
                        :href="users.prev_page_url"
                        class="px-3 py-1 border border-gray-200 rounded hover:bg-gray-50"
                    >← Prev</Link>
                    <Link
                        v-if="users.next_page_url"
                        :href="users.next_page_url"
                        class="px-3 py-1 border border-gray-200 rounded hover:bg-gray-50"
                    >Next →</Link>
                </div>
            </div>
        </div>

        <!-- Invite Modal -->
        <teleport to="body">
            <div v-if="showInviteModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Invite Team Member</h2>
                    <form @submit.prevent="invite" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input v-model="inviteForm.name" type="text" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="inviteForm.errors.name" class="text-red-500 text-xs mt-1">{{ inviteForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input v-model="inviteForm.email" type="email" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p v-if="inviteForm.errors.email" class="text-red-500 text-xs mt-1">{{ inviteForm.errors.email }}</p>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="showInviteModal = false" class="flex-1 border border-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-50">Cancel</button>
                            <button type="submit" :disabled="inviteForm.processing" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                                {{ inviteForm.processing ? 'Inviting…' : 'Send Invite' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </teleport>
    </AppLayout>
</template>
