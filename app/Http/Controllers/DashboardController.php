<?php

namespace App\Http\Controllers;

use App\Models\RevenueSnapshot;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app('tenant');
        $user = $request->user();

        $totalUsers = $tenant->users()->count();
        $activeUsers = $tenant->users()->active()->count();

        // MRR from latest snapshot
        $latestSnapshot = $tenant->revenueSnapshots()->latest('month')->first();
        $prevSnapshot = $tenant->revenueSnapshots()->orderBy('month', 'desc')->skip(1)->first();

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
                'month' => \Carbon\Carbon::parse($s->month . '-01')->format('M Y'),
                'mrr'   => (float) $s->mrr,
            ])
            ->values();

        return Inertia::render('Dashboard', [
            'total_users'   => $totalUsers,
            'active_users'  => $activeUsers,
            'mrr'           => (float) $mrr,
            'churn_rate'    => $churnRate,
            'revenue_chart' => $revenueChart,
        ]);
    }
}
