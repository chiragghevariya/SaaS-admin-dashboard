<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    has_feature: Boolean,
    plan:        String,
    api_keys:    Array,
});

const showNewKeyModal = ref(false);
const newKeyForm = useForm({ name: '' });

function createKey() {
    newKeyForm.post(route('integrations.api-keys.store'), {
        onSuccess: () => { showNewKeyModal.value = false; newKeyForm.reset(); },
    });
}

function revokeKey(id) {
    if (confirm('Revoke this API key? This cannot be undone.')) {
        router.delete(route('integrations.api-keys.destroy', id), { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Integrations" />
    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Integrations</h1>
        </template>

        <div class="max-w-3xl space-y-6">

            <!-- ── Locked state (non-Enterprise) ── -->
            <div v-if="!has_feature" class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
                <div class="w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Custom Integrations</h2>
                <p class="text-sm text-gray-500 mb-2">API key management and webhook endpoints are available on the <strong>Enterprise</strong> plan.</p>
                <p class="text-xs text-gray-400 mb-6">Your current plan: <span class="font-semibold capitalize">{{ plan ?? 'None' }}</span></p>
                <Link :href="route('billing.plans')" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors">
                    Upgrade to Enterprise
                </Link>
            </div>

            <!-- ── Enterprise: API Keys ── -->
            <template v-else>

                <!-- API Keys card -->
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">API Keys</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Use these keys to authenticate requests from your own systems.</p>
                        </div>
                        <button
                            @click="showNewKeyModal = true"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors"
                        >
                            + New Key
                        </button>
                    </div>

                    <!-- Empty state -->
                    <p v-if="api_keys.length === 0" class="text-sm text-gray-400 text-center py-8">
                        No API keys yet. Create your first key to get started.
                    </p>

                    <!-- Key list -->
                    <ul v-else class="divide-y divide-gray-100">
                        <li v-for="key in api_keys" :key="key.id" class="py-4 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ key.name }}</p>
                                <p class="text-xs font-mono text-gray-400 mt-0.5">{{ key.masked_key }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-xs text-gray-400">Created {{ key.created_at }}</p>
                                <p v-if="key.last_used_at" class="text-xs text-gray-400">Last used {{ key.last_used_at }}</p>
                                <p v-else class="text-xs text-gray-300">Never used</p>
                            </div>
                            <button
                                @click="revokeKey(key.id)"
                                class="shrink-0 text-xs text-red-500 hover:text-red-700 font-medium"
                            >
                                Revoke
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Webhooks placeholder -->
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-1">
                        <h2 class="text-base font-semibold text-gray-900">Webhooks</h2>
                        <span class="text-xs text-gray-400 bg-gray-100 rounded-full px-2 py-0.5">Coming soon</span>
                    </div>
                    <p class="text-sm text-gray-500">Configure HTTP endpoints to receive real-time event notifications from your workspace.</p>
                </div>

            </template>
        </div>

        <!-- ── Create Key Modal ── -->
        <teleport to="body">
            <div v-if="showNewKeyModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Create API Key</h2>
                    <form @submit.prevent="createKey" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Key Name</label>
                            <input
                                v-model="newKeyForm.name"
                                type="text"
                                placeholder="e.g. Production Server"
                                required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            <p v-if="newKeyForm.errors.name" class="text-red-500 text-xs mt-1">{{ newKeyForm.errors.name }}</p>
                        </div>
                        <div class="flex gap-3 pt-1">
                            <button type="button" @click="showNewKeyModal = false" class="flex-1 border border-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" :disabled="newKeyForm.processing" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                                {{ newKeyForm.processing ? 'Creating…' : 'Create Key' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </teleport>
    </AppLayout>
</template>
