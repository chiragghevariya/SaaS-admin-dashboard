<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    subscription:   Object,
    payment_method: Object,
});

const showCancelModal = ref(false);

function manageBilling() {
    router.post(route('billing.portal.redirect'));
}

function cancelSubscription() {
    router.post(route('billing.cancel'), {}, {
        onSuccess: () => { showCancelModal.value = false; }
    });
}

const statusColor = (status) => ({
    active:    'bg-emerald-50 text-emerald-700',
    trialing:  'bg-blue-50 text-blue-700',
    canceled:  'bg-red-50 text-red-700',
    past_due:  'bg-yellow-50 text-yellow-700',
})[status] ?? 'bg-gray-100 text-gray-500';
</script>

<template>
    <Head title="Billing" />
    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Billing & Subscription</h1>
        </template>

        <div class="max-w-2xl">
            <!-- No subscription state -->
            <div v-if="!subscription" class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
                <div class="text-4xl mb-3">💳</div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">No active subscription</h2>
                <p class="text-gray-500 text-sm mb-6">Choose a plan to unlock all features.</p>
                <Link :href="route('billing.plans')" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700">
                    View Plans
                </Link>
            </div>

            <!-- Active subscription -->
            <div v-else class="space-y-4">
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-semibold text-gray-900">Current Subscription</h2>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize" :class="statusColor(subscription.status)">
                            {{ subscription.status }}
                        </span>
                    </div>

                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500 mb-0.5">Plan</dt>
                            <dd class="font-semibold text-gray-900">{{ subscription.plan_name }}</dd>
                        </div>
                        <div v-if="subscription.trial_ends_at">
                            <dt class="text-gray-500 mb-0.5">Trial ends</dt>
                            <dd class="font-semibold text-gray-900">{{ subscription.trial_ends_at }}</dd>
                        </div>
                        <div v-if="subscription.ends_at">
                            <dt class="text-gray-500 mb-0.5">Access until</dt>
                            <dd class="font-semibold text-gray-900">{{ subscription.ends_at }}</dd>
                        </div>
                        <div v-if="payment_method?.last_four">
                            <dt class="text-gray-500 mb-0.5">Payment method</dt>
                            <dd class="font-semibold text-gray-900 capitalize">
                                {{ payment_method.type }} ···· {{ payment_method.last_four }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="flex gap-3">
                    <button
                        @click="manageBilling"
                        class="flex-1 bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors"
                    >
                        Manage Billing
                    </button>
                    <button
                        v-if="subscription.status === 'active' || subscription.status === 'trialing'"
                        @click="showCancelModal = true"
                        class="flex-1 border border-red-200 text-red-600 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-50 transition-colors"
                    >
                        Cancel Subscription
                    </button>
                </div>
            </div>
        </div>

        <!-- Cancel confirmation modal -->
        <teleport to="body">
            <div v-if="showCancelModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Cancel Subscription?</h2>
                    <p class="text-sm text-gray-500 mb-6">You will retain access until the end of your current billing period.</p>
                    <div class="flex gap-3">
                        <button @click="showCancelModal = false" class="flex-1 border border-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm hover:bg-gray-50">
                            Keep Subscription
                        </button>
                        <button @click="cancelSubscription" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-red-700">
                            Yes, Cancel
                        </button>
                    </div>
                </div>
            </div>
        </teleport>
    </AppLayout>
</template>
