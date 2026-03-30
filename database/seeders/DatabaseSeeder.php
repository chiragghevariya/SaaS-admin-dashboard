<?php

namespace Database\Seeders;

use App\Models\RevenueSnapshot;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRolesAndPermissions();

        // Acme Corp — primary demo tenant
        $acme = Tenant::create(['name' => 'Acme Corp', 'slug' => 'acme']);

        $admin = User::create([
            'tenant_id' => $acme->id, 'name' => 'Alex Admin',
            'email' => 'admin@demo.com', 'password' => Hash::make('password'), 'status' => 'active',
        ]);
        $admin->assignRole('admin');

        $member = User::create([
            'tenant_id' => $acme->id, 'name' => 'Maria Member',
            'email' => 'member@demo.com', 'password' => Hash::make('password'), 'status' => 'active',
        ]);
        $member->assignRole('member');

        $fakeUsers = [
            ['name' => 'Jordan Rivera',   'email' => 'jordan@acmecorp.com'],
            ['name' => 'Casey Chen',      'email' => 'casey@acmecorp.com'],
            ['name' => 'Morgan Williams', 'email' => 'morgan@acmecorp.com'],
            ['name' => 'Taylor Johnson',  'email' => 'taylor@acmecorp.com'],
            ['name' => 'Sam Rodriguez',   'email' => 'sam@acmecorp.com'],
            ['name' => 'Dana Kim',        'email' => 'dana@acmecorp.com'],
            ['name' => 'Riley Thompson',  'email' => 'riley@acmecorp.com'],
            ['name' => 'Cameron Davis',   'email' => 'cameron@acmecorp.com'],
        ];

        foreach ($fakeUsers as $userData) {
            $u = User::create([
                'tenant_id' => $acme->id, 'name' => $userData['name'],
                'email' => $userData['email'], 'password' => Hash::make('password'),
                'status' => rand(0, 3) > 0 ? 'active' : 'inactive',
            ]);
            $u->assignRole('member');
        }

        $mrrData = [
            ['month' => '2024-10', 'mrr' => 2400, 'new' => 4, 'churned' => 0],
            ['month' => '2024-11', 'mrr' => 3100, 'new' => 6, 'churned' => 1],
            ['month' => '2024-12', 'mrr' => 3600, 'new' => 5, 'churned' => 0],
            ['month' => '2025-01', 'mrr' => 4200, 'new' => 7, 'churned' => 1],
            ['month' => '2025-02', 'mrr' => 5100, 'new' => 9, 'churned' => 2],
            ['month' => '2025-03', 'mrr' => 5800, 'new' => 8, 'churned' => 1],
        ];

        foreach ($mrrData as $row) {
            RevenueSnapshot::create([
                'tenant_id' => $acme->id, 'month' => $row['month'],
                'mrr' => $row['mrr'], 'new_customers' => $row['new'],
                'churned_customers' => $row['churned'],
            ]);
        }

        // Second tenant — shows isolation
        $tech = Tenant::create(['name' => 'TechStartup', 'slug' => 'techstartup']);
        $techAdmin = User::create([
            'tenant_id' => $tech->id, 'name' => 'Taylor Tech',
            'email' => 'admin@techstartup.com', 'password' => Hash::make('password'), 'status' => 'active',
        ]);
        $techAdmin->assignRole('admin');
    }

    private function seedRolesAndPermissions(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['manage-users', 'manage-billing', 'view-reports'] as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $adminRole  = Role::firstOrCreate(['name' => 'admin']);
        $memberRole = Role::firstOrCreate(['name' => 'member']);

        $superAdmin->syncPermissions(['manage-users', 'manage-billing', 'view-reports']);
        $adminRole->syncPermissions(['manage-users', 'view-reports']);
        $memberRole->syncPermissions(['view-reports']);
    }
}
