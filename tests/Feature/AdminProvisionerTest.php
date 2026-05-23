<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AdminProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProvisionerTest extends TestCase
{
    use RefreshDatabase;

    public function test_skips_when_env_not_configured(): void
    {
        config([
            'portal.admin.email' => null,
            'portal.admin.password' => null,
        ]);

        $result = app(AdminProvisioner::class)->ensure();

        $this->assertSame('skipped', $result['status']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_creates_admin_from_env(): void
    {
        config([
            'portal.admin.name' => 'Root Admin',
            'portal.admin.email' => 'admin@example.com',
            'portal.admin.password' => 'secret-password',
        ]);

        $result = app(AdminProvisioner::class)->ensure();

        $this->assertSame('created', $result['status']);
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'name' => 'Root Admin',
            'permissions' => User::PERM_ADMIN,
        ]);

        $user = User::where('email', 'admin@example.com')->first();
        $this->assertTrue($user->isAdmin());
    }

    public function test_updates_existing_admin(): void
    {
        config([
            'portal.admin.name' => 'Updated Admin',
            'portal.admin.email' => 'admin@example.com',
            'portal.admin.password' => 'new-password',
        ]);

        User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Old Name',
            'permissions' => User::PERM_STUDENT,
        ]);

        $result = app(AdminProvisioner::class)->ensure();

        $this->assertSame('updated', $result['status']);
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'name' => 'Updated Admin',
            'permissions' => User::PERM_ADMIN,
        ]);
    }
}
