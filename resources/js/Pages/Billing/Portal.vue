<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    subscription:   Object,
    payment_method: Object,
});

const showCancelModal  = ref(false);
const managingBilling  = ref(false);

function manageBilling() {
    managingBilling.value = true;
    router.post(route('billing.portal.redirect'), {}, {
        onFinish: () => { managingBilling.value = false; },
    });
}

function cancelSubscription() {
    router.post(route('billing.cancel'), {}, {
        onSuccess: () => { showCancelModal.value = false; },
    });
}

function resumeSubscription() {
    router.post(route('billing.resume'));
}

// Derive a human-readable label and colour for the status badge.
// After soft-cancel, stripe_status is still "active" but on_grace_period is true.
function statusLabel(sub) {
    if (sub.on_grace_period) return 'Cancelled';
    if (sub.cancelled)       return 'Expired';
    return sub.status ? sub.status.charAt(0).toUpperCase() + sub.status.slice(1).replace('_', ' ') : '—';
}

function statusColor(sub) {
    if (sub.on_grace_period) return 'bg-orange-50 text-orange-700';
    if (sub.cancelled)       return 'bg-red-50 text-red-700';
    return {
        active:   'bg-emerald-50 text-emerald-700',
        trialing: 'bg-blue-50 text-blue-700',
        past_due: 'bg-yellow-50 text-yellow-700',
    }[sub.status] ?? 'bg-gray-100 text-gray-500';
}
</script>

<template>
    <Head title="Billing" />
    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Billing &amp; Subscription</h1>
        </template>

        <div class="max-w-2xl space-y-4">

            <!-- ── No subscription ── -->
            <div v-if="!subscription" class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
                <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">No active subscription</h2>
                <p class="text-gray-500 text-sm mb-6">Choose a plan to unlock all features.</p>
                <Link :href="route('billing.plans')" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors">
                    View Plans
                </Link>
            </div>

            <!-- ── Active subscription ── -->
            <template v-else>

                <!-- Subscription details card -->
                <div class="bg-white rounded-2xl border border-gray-200 p-6">

                    <!-- Header row: plan name + status badge -->
                    <div class="flex items-start justify-between mb-5">
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Current Plan</p>
                            <h2 class="text-xl font-bold text-gray-900">
                                {{ subscription.plan_name }}
                                <span v-if="subscription.plan_price" class="text-base font-normal text-gray-500">
                                    — ${{ subscription.plan_price }}/mo
                                </span>
                            </h2>
                        </div>
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold shrink-0 mt-1"
                            :class="statusColor(subscription)"
                        >
                            {{ statusLabel(subscription) }}
                        </span>
                    </div>

                    <!-- Detail grid -->
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm border-t border-gray-100 pt-4">

                        <!-- Next billing / ends at -->
                        <div v-if="subscription.next_billing_at && !subscription.on_grace_period">
                            <dt class="text-gray-500 mb-0.5">Next billing date</dt>
                            <dd class="font-semibold text-gray-900">{{ subscription.next_billing_at }}</dd>
                        </div>
                        <div v-else-if="subscription.on_grace_period && subscription.ends_at">
                            <dt class="text-gray-500 mb-0.5">Access until</dt>
                            <dd class="font-semibold text-orange-700">{{ subscription.ends_at }}</dd>
                        </div>

                        <!-- Trial end date -->
                        <div v-if="subscription.trial_ends_at">
                            <dt class="text-gray-500 mb-0.5">Trial ends</dt>
                            <dd class="font-semibold text-gray-900">{{ subscription.trial_ends_at }}</dd>
                        </div>

                        <!-- Payment method -->
                        <div v-if="payment_method?.last_four">
                            <dt class="text-gray-500 mb-0.5">Payment method</dt>
                            <dd class="font-semibold text-gray-900 capitalize flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                {{ payment_method.type }} &bull;&bull;&bull;&bull; {{ payment_method.last_four }}
                            </dd>
                        </div>

                    </dl>

                    <!-- Grace-period notice -->
                    <div v-if="subscription.on_grace_period" class="mt-4 rounded-xl bg-orange-50 border border-orange-100 px-4 py-3 text-sm text-orange-700">
                        Your subscription is cancelled and will not renew. You can still use all features until
                        <strong>{{ subscription.ends_at }}</strong>.
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="flex gap-3">
                    <!-- Manage Billing → opens Stripe Customer Portal -->
                    <button
                        @click="manageBilling"
                        :disabled="managingBilling"
                        class="flex-1 bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 disabled:opacity-60 transition-colors"
                    >
                        {{ managingBilling ? 'Redirecting…' : 'Manage Billing' }}
                    </button>

                    <!-- Resume Auto-Renew (shown when on grace period) -->
                    <button
                        v-if="subscription.on_grace_period"
                        @click="resumeSubscription"
                        class="flex-1 border border-emerald-300 text-emerald-700 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-emerald-50 transition-colors"
                    >
                        Resume Auto-Renew
                    </button>

                    <!-- Cancel Subscription (shown when actively subscribed, not yet cancelled) -->
                    <button
                        v-else-if="subscription.status === 'active' || subscription.status === 'trialing'"
                        @click="showCancelModal = true"
                        class="flex-1 border border-red-200 text-red-600 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-50 transition-colors"
                    >
                        Cancel Subscription
                    </button>
                </div>

            </template>
        </div>

        <!-- ── Cancel confirmation modal ── -->
        <teleport to="body">
            <div v-if="showCancelModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Cancel Subscription?</h2>
                    <p class="text-sm text-gray-500 mb-6">
                        Your subscription will not renew. You will keep full access until the end of the current billing period
                        <template v-if="subscription?.next_billing_at">
                            (<strong>{{ subscription.next_billing_at }}</strong>)
                        </template>.
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="showCancelModal = false"
                            class="flex-1 border border-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm hover:bg-gray-50"
                        >
                            Keep Subscription
                        </button>
                        <button
                            @click="cancelSubscription"
                            class="flex-1 bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-red-700"
                        >
                            Yes, Cancel
                        </button>
                    </div>
                </div>
            </div>
        </teleport>
    </AppLayout>
</template>
