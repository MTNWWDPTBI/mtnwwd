<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_general_authenticated_routes_render_successfully(): void
    {
        $user = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $routes = [
            'dashboard',
            'sparepart.catalog',
            'wwd-projects.index',
            'wwd-projects.create',
            'tpm.index',
            'tpm.scan',
            'handover.index',
            'handover.create',
            'assistant.index',
        ];

        foreach ($routes as $routeName) {
            $response = $this->actingAs($user)->get(route($routeName));
            $this->assertTrue(
                $response->isOk(),
                "Route [{$routeName}] failed with status: {$response->status()}"
            );
        }
    }

    public function test_all_admin_restricted_routes_render_successfully(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $adminRoutes = [
            'spareparts.index',
            'spareparts.create',
            'assemblies.index',
            'assemblies.create',
            'machines.index',
            'machines.create',
            'tpm.create',
            'import.index',
            'users.index',
            'users.create',
            'export.tpm',
            'export.tpm.transactions',
            'export.handover',
        ];

        foreach ($adminRoutes as $routeName) {
            $response = $this->actingAs($admin)->get(route($routeName));
            $this->assertTrue(
                $response->isOk(),
                "Admin Route [{$routeName}] failed with status: {$response->status()}"
            );
        }
    }

    public function test_admin_can_run_database_migrations_via_browser(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.migrate'));
        $response->assertOk();
        $response->assertSee('DATABASE MIGRATION COMPLETED');
    }

    public function test_non_admin_cannot_run_database_migrations_via_browser(): void
    {
        $user = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $response = $this->actingAs($user)->get(route('admin.migrate'));
        $response->assertForbidden();
    }
}
