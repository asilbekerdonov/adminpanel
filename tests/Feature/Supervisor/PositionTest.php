<?php

namespace Tests\Feature\Supervisor;

use App\Models\Position;
use App\Models\Subdivision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PositionTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected Subdivision $subdivision;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['position.view', 'position.manage', 'subdivision.view', 'subdivision.manage'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web'])
            ->syncPermissions(Permission::all());

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->subdivision = Subdivision::factory()->create();
    }

    #[Test]
    public function super_admin_can_view_positions_page(): void
    {
        Position::factory()->count(3)->create(['subdivision_id' => $this->subdivision->id]);

        $this->actingAs($this->superAdmin)
            ->get(route('positions.index', ['subdivision_id' => $this->subdivision->id]))
            ->assertOk()
            ->assertViewIs('structure.positions')
            ->assertViewHas('subdivision')
            ->assertViewHas('positions');
    }

    #[Test]
    public function guest_cannot_view_positions_page(): void
    {
        $this->get(route('positions.index', ['subdivision_id' => $this->subdivision->id]))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function super_admin_can_create_position_without_user(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('positions.store'), [
                'subdivision_id' => $this->subdivision->id,
                'name' => 'Менеджер',
                'category' => 'B',
                'grade' => 3,
            ])
            ->assertRedirect(route('positions.index', ['subdivision_id' => $this->subdivision->id]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('positions', [
            'subdivision_id' => $this->subdivision->id,
            'name' => 'Менеджер',
            'category' => 'B',
            'grade' => 3,
            'is_vacant' => true,
        ]);
    }

    #[Test]
    public function super_admin_can_create_position_with_user(): void
    {
        Role::firstOrCreate(['name' => 'hr_manager', 'guard_name' => 'web']);

        $this->actingAs($this->superAdmin)
            ->post(route('positions.store'), [
                'subdivision_id' => $this->subdivision->id,
                'name' => 'HR Специалист',
                'category' => 'A',
                'grade' => 2,
                'user_name' => 'Иван Иванов',
                'email' => 'ivan@example.com',
                'password' => 'secret123',
                'role' => 'hr_manager',
            ])
            ->assertRedirect(route('positions.index', ['subdivision_id' => $this->subdivision->id]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('positions', [
            'subdivision_id' => $this->subdivision->id,
            'name' => 'HR Специалист',
            'is_vacant' => false,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'ivan@example.com',
            'name' => 'Иван Иванов',
        ]);
    }

    #[Test]
    public function guest_cannot_create_position(): void
    {
        $this->post(route('positions.store'), [
            'subdivision_id' => $this->subdivision->id,
            'name' => 'Менеджер',
            'category' => 'B',
            'grade' => 3,
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('positions', ['name' => 'Менеджер']);
    }

    #[Test]
    public function super_admin_can_delete_position(): void
    {
        $position = Position::factory()->create(['subdivision_id' => $this->subdivision->id]);

        $this->actingAs($this->superAdmin)
            ->delete(route('positions.destroy', $position))
            ->assertRedirect(route('positions.index', ['subdivision_id' => $this->subdivision->id]))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('positions', ['id' => $position->id]);
    }

    #[Test]
    public function super_admin_cannot_delete_own_position(): void
    {
        $position = Position::factory()->create(['subdivision_id' => $this->subdivision->id]);
        $this->superAdmin->update(['position_id' => $position->id]);

        $this->actingAs($this->superAdmin)
            ->delete(route('positions.destroy', $position))
            ->assertRedirect(route('positions.index', ['subdivision_id' => $this->subdivision->id]))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('positions', ['id' => $position->id]);
    }

    #[Test]
    public function guest_cannot_delete_position(): void
    {
        $position = Position::factory()->create(['subdivision_id' => $this->subdivision->id]);

        $this->delete(route('positions.destroy', $position))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('positions', ['id' => $position->id]);
    }
}
