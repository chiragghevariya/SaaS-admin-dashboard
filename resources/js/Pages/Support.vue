<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    plan:     String, // 'starter' | 'pro' | 'enterprise' | null
    features: Object,
});

const tiers = [
    {
        title:       'Email Support',
        description: 'Send us a message and our team will get back to you.',
        sla:         'Response within 2–3 business days',
        action:      'mailto:support@example.com',
        actionLabel: 'Send Email',
        available:   true,
        badge:       null,
    },
    {
        title:       'Priority Support',
        description: 'Jump to the front of the queue with priority email handling.',
        sla:         'Response within 24 hours',
        action:      'mailto:priority@example.com',
        actionLabel: 'Contact Priority Support',
        available:   props.features?.priority_support ?? false,
        badge:       'Pro',
        badgeColor:  'bg-indigo-100 text-indigo-700',
    },
    {
        title:       'Dedicated Account Manager',
        description: 'Your own named point of contact for strategic guidance and onboarding.',
        sla:         'Same-business-day response + monthly check-in calls',
        action:      'mailto:enterprise@example.com',
        actionLabel: 'Contact Your Manager',
        available:   props.features?.sla_guarantee ?? false,
        badge:       'Enterprise',
        badgeColor:  'bg-amber-100 text-amber-700',
    },
];
</script>

<template>
    <Head title="Support" />
    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Support</h1>
        </template>

        <div class="max-w-3xl space-y-4">

            <!-- Current plan badge -->
            <div class="flex items-center gap-2 mb-2">
                <span class="text-sm text-gray-500">Your plan:</span>
                <span
                    class="text-xs font-semibold rounded-full px-2.5 py-0.5 capitalize"
                    :class="{
                        'bg-gray-100 text-gray-500'     : !plan,
                        'bg-blue-50 text-blue-700'      : plan === 'starter',
                        'bg-indigo-50 text-indigo-700'  : plan === 'pro',
                        'bg-amber-50 text-amber-700'    : plan === 'enterprise',
                    }"
                >
                    {{ plan ? plan : 'No Plan' }}
                </span>
            </div>

            <!-- Support tier cards -->
            <div
                v-for="tier in tiers"
                :key="tier.title"
                :class="[
                    'bg-white rounded-2xl border p-6 transition-all',
                    tier.available ? 'border-gray-200' : 'border-gray-100 opacity-60',
                ]"
            >
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-semibold text-gray-900">{{ tier.title }}</h2>
                        <span
                            v-if="tier.badge"
                            class="text-[10px] font-semibold rounded px-1.5 py-0.5"
                            :class="tier.badgeColor"
                        >{{ tier.badge }}</span>
                    </div>
                    <span v-if="tier.available" class="text-xs font-semibold text-emerald-600 bg-emerald-50 rounded-full px-2 py-0.5">Available</span>
                    <span v-else class="text-xs font-semibold text-gray-400 bg-gray-50 rounded-full px-2 py-0.5">Locked</span>
                </div>

                <p class="text-sm text-gray-600 mb-3">{{ tier.description }}</p>

                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-400 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ tier.sla }}
                    </p>

                    <a
                        v-if="tier.available"
                        :href="tier.action"
                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-800"
                    >
                        {{ tier.actionLabel }} →
                    </a>
                    <Link
                        v-else
                        :href="route('billing.plans')"
                        class="text-xs font-semibold text-gray-400 hover:text-indigo-600"
                    >
                        Upgrade to unlock →
                    </Link>
                </div>
            </div>

            <!-- SLA section (Enterprise) -->
            <div v-if="features?.sla_guarantee" class="bg-amber-50 border border-amber-100 rounded-2xl p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    SLA Guarantee
                    <span class="text-[10px] font-semibold bg-amber-100 text-amber-700 rounded px-1.5 py-0.5">Enterprise</span>
                </h2>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        99.9% uptime guarantee
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Same-business-day incident response
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Service credits for downtime exceeding SLA
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Monthly service review calls
                    </li>
                </ul>
            </div>

        </div>
    </AppLayout>
</template>
