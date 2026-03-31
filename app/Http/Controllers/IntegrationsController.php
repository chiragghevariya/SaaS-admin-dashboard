<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class IntegrationsController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');

        return Inertia::render('Integrations/Index', [
            'has_feature' => $tenant->hasFeature('custom_integrations'),
            'plan'        => $tenant->activePlan(),
            'api_keys'    => $tenant->hasFeature('custom_integrations')
                ? $tenant->apiKeys()
                    ->latest()
                    ->get(['id', 'name', 'key', 'last_used_at', 'created_at'])
                    ->map(fn($k) => [
                        'id'           => $k->id,
                        'name'         => $k->name,
                        'masked_key'   => $k->maskedKey(),
                        'last_used_at' => $k->last_used_at?->diffForHumans(),
                        'created_at'   => $k->created_at->format('M d, Y'),
                    ])
                : [],
        ]);
    }

    public function store(Request $request)
    {
        $tenant = app('tenant');
        abort_unless($tenant->hasFeature('custom_integrations'), 403, 'Enterprise plan required.');

        $validated = $request->validate(['name' => 'required|string|max:100']);

        $tenant->apiKeys()->create([
            'name' => $validated['name'],
            'key'  => 'sk_live_' . Str::random(48),
        ]);

        return redirect()->back()->with('success', "API key \"{$validated['name']}\" created.");
    }

    public function destroy(ApiKey $apiKey)
    {
        $tenant = app('tenant');
        abort_if($apiKey->tenant_id !== $tenant->id, 403, 'Access denied.');

        $name = $apiKey->name;
        $apiKey->delete();

        return redirect()->back()->with('success', "API key \"{$name}\" revoked.");
    }
}
