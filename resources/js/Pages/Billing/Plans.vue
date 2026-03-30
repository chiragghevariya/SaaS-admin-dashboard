<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    plans:               Array,
    current_plan:        String,
    subscription_status: String,
});

const loading = ref(null);

function subscribe(priceId) {
    loading.value = priceId;
    router.post(route('billing.checkout'), { price_id: priceId });
}

const isCurrentPlan = (plan) => plan.price_id === props.current_plan;
const isActive = props.subscription_status === 'active' || props.subscription_status === 'trialing';
</script>

<template>
    <Head title="Billing Plans" />
    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Choose a Plan</h1>
        </template>

        <div class="max-w-5xl mx-auto">
            <p class="text-center text-gray-500 mb-10">Simple, transparent pricing. No hidden fees.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="plan in plans"
                    :key="plan.name"
                    :class="[
                        'rounded-2xl border-2 p-6 flex flex-col',
                        plan.highlighted ? 'border-indigo-500 shadow-lg' : 'border-gray-200 bg-white',
                        isCurrentPlan(plan) ? 'ring-2 ring-indigo-600' : '',
                    ]"
                >
                    <div v-if="plan.highlighted" class="mb-2">
                        <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">
                            Most Popular
                        </span>
                    </div>

                    <h2 class="text-xl font-bold text-gray-900">{{ plan.name }}</h2>
                    <div class="mt-2 mb-4">
                        <span class="text-4xl font-extrabold text-gray-900">${{ plan.price }}</span>
                        <span class="text-gray-500">/month</span>
                    </div>

                    <ul class="space-y-2 mb-6 flex-1">
                        <li v-for="feature in plan.features" :key="feature" class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ feature }}
                        </li>
                    </ul>

                    <div>
                        <span
                            v-if="isCurrentPlan(plan) && isActive"
                            class="w-full inline-flex items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 px-4 py-3 text-sm font-semibold"
                        >
                            ✓ Current Plan
                        </span>
                        <button
                            v-else
                            @click="subscribe(plan.price_id)"
                            :disabled="loading === plan.price_id"
                            :class="[
                                'w-full rounded-xl px-4 py-3 text-sm font-semibold transition-colors',
                                plan.highlighted
                                    ? 'bg-indigo-600 text-white hover:bg-indigo-700'
                                    : 'bg-gray-100 text-gray-800 hover:bg-gray-200',
                            ]"
                        >
                            {{ loading === plan.price_id ? 'Redirecting…' : 'Upgrade' }}
                        </button>
                    </div>
                </div>
            </div>

            <p class="text-center text-xs text-gray-400 mt-8">
                Test card: 4242 4242 4242 4242 · Any future date · Any CVC
            </p>
        </div>
    </AppLayout>
</template>
