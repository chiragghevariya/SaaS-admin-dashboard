<?php

namespace App\Http\Controllers;

use App\Models\RevenueSnapshot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app('tenant');

        $totalUsers  = $tenant->users()->count();
        $activeUsers = $tenant->users()->active()->count();

        // MRR from latest snapshot
        $latestSnapshot = $tenant->revenueSnapshots()->latest('month')->first();
        $prevSnapshot   = $tenant->revenueSnapshots()->orderBy('month', 'desc')->skip(1)->first();

        $mrr = $latestSnapshot?->mrr ?? 0;

        // Churn rate: churned / (prev month customers)
        $churnRate = 0;
        if ($prevSnapshot && $prevSnapshot->new_customers > 0) {
            $churnRate = round(($latestSnapshot?->churned_customers ?? 0) / max($prevSnapshot->new_customers, 1) * 100, 1);
        }

        // Revenue chart: last 6 months
        $revenueChart = $tenant->revenueSnapshots()
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get()
            ->reverse()
            ->map(fn($s) => [
                'month' => Carbon::parse($s->month . '-01')->format('M Y'),
                'mrr'   => (float) $s->mrr,
            ])
            ->values();

        // ── Advanced analytics (Pro / Enterprise only) ──────────────────────
        $planFeatures = [
            'advanced_analytics'  => $tenant->hasFeature('advanced_analytics'),
            'priority_support'    => $tenant->hasFeature('priority_support'),
            'custom_integrations' => $tenant->hasFeature('custom_integrations'),
            'sla_guarantee'       => $tenant->hasFeature('sla_guarantee'),
        ];

        $advancedAnalytics = null;

        if ($planFeatures['advanced_analytics']) {
            $revenueGrowth = 0;
            if ($prevSnapshot && (float) $prevSnapshot->mrr > 0) {
                $revenueGrowth = round(
                    ((float) ($latestSnapshot?->mrr ?? 0) - (float) $prevSnapshot->mrr)
                    / (float) $prevSnapshot->mrr * 100,
                    1
                );
            }

            $customerChart = $tenant->revenueSnapshots()
                ->orderBy('month', 'desc')
                ->limit(6)
                ->get()
                ->reverse()
                ->map(fn($s) => [
                    'month'    => Carbon::parse($s->month . '-01')->format('M Y'),
                    'new'      => (int) $s->new_customers,
                    'churned'  => (int) $s->churned_customers,
                ])
                ->values();

            $advancedAnalytics = [
                'revenue_growth'       => $revenueGrowth,
                'new_customers'        => (int) ($latestSnapshot?->new_customers ?? 0),
                'churned_customers'    => (int) ($latestSnapshot?->churned_customers ?? 0),
                'avg_revenue_per_user' => $activeUsers > 0
                    ? round((float) $mrr / $activeUsers, 2)
                    : 0,
                'customer_chart'       => $customerChart,
            ];
        }

        return Inertia::render('Dashboard', [
            'total_users'        => $totalUsers,
            'active_users'       => $activeUsers,
            'mrr'                => (float) $mrr,
            'churn_rate'         => $churnRate,
            'revenue_chart'      => $revenueChart,
            'plan_features'      => $planFeatures,
            'advanced_analytics' => $advancedAnalytics,
        ]);
    }
}
