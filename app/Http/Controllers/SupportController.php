<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class SupportController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');

        return Inertia::render('Support', [
            'plan'     => $tenant->activePlan(),
            'features' => [
                'priority_support' => $tenant->hasFeature('priority_support'),
                'sla_guarantee'    => $tenant->hasFeature('sla_guarantee'),
            ],
        ]);
    }
}
