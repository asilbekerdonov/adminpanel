<?php

namespace Tests\Feature\Supervisor;

use App\Models\Department;
use App\Models\Position;
use App\Models\Subdivision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SubdivisionTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['subdivision.view', 'subdivision.manage', 'department.view', 'department.manage'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web'])
            ->syncPermissions(Permission::all());

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->department = Department::factory()->create();
    }

    #[Test]
    public function super_admin_can_view_subdivisions_page(): void
    {
        Subdivision::factory()->count(3)->create(['department_id' => $this->department->id]);

        $this->actingAs($this->superAdmin)
            ->get(route('subdivisions.index', ['department_id' => $this->department->id]))
            ->assertOk()
            ->assertViewIs('structure.subdivisions')
            ->assertViewHas('department')
            ->assertViewHas('subdivisions');
    }

    #[Test]
    public function guest_cannot_view_subdivisions_page(): void
    {
        $this->get(route('subdivisions.index', ['department_id' => $this->department->id]))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function super_admin_can_create_subdivision(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('subdivisions.store'), [
                'department_id' => $this->department->id,
                'name' => 'Бухгалтерия',
                'code' => 'ACC-001',
            ])
            ->assertRedirect(route('subdivisions.index', ['department_id' => $this->department->id]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('subdivisions', [
            'department_id' => $this->department->id,
            'name' => 'Бухгалтерия',
            'code' => 'ACC-001',
        ]);
    }

    #[Test]
    public function guest_cannot_create_subdivision(): void
    {
        $this->post(route('subdivisions.store'), [
            'department_id' => $this->department->id,
            'name' => 'Бухгалтерия',
            'code' => 'ACC-001',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('subdivisions', ['code' => 'ACC-001']);
    }

    #[Test]
    public function super_admin_can_delete_subdivision(): void
    {
        $subdivision = Subdivision::factory()->create(['department_id' => $this->department->id]);

        $this->actingAs($this->superAdmin)
            ->delete(route('subdivisions.destroy', $subdivision))
            ->assertRedirect(route('subdivisions.index', ['department_id' => $this->department->id]))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('subdivisions', ['id' => $subdivision->id]);
    }

    #[Test]
    public function super_admin_cannot_delete_subdivision_that_has_positions(): void
    {
        $subdivision = Subdivision::factory()->create(['department_id' => $this->department->id]);
        Position::factory()->create(['subdivision_id' => $subdivision->id]);

        $this->actingAs($this->superAdmin)
            ->delete(route('subdivisions.destroy', $subdivision))
            ->assertRedirect(route('subdivisions.index', ['department_id' => $this->department->id]))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('subdivisions', ['id' => $subdivision->id]);
    }

    #[Test]
    public function guest_cannot_delete_subdivision(): void
    {
        $subdivision = Subdivision::factory()->create(['department_id' => $this->department->id]);

        $this->delete(route('subdivisions.destroy', $subdivision))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('subdivisions', ['id' => $subdivision->id]);
    }
}
