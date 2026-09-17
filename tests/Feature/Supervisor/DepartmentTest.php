<?php

namespace Tests\Feature\Supervisor;

use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['department.view', 'department.manage', 'branch.view', 'branch.manage'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web'])
            ->syncPermissions(Permission::all());

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->branch = Branch::factory()->create();
    }

    #[Test]
    public function super_admin_can_view_departments_page(): void
    {
        Department::factory()->count(3)->create(['branch_id' => $this->branch->id]);

        $this->actingAs($this->superAdmin)
            ->get(route('departments.index', ['branch_id' => $this->branch->id]))
            ->assertOk()
            ->assertViewIs('structure.departments')
            ->assertViewHas('branch')
            ->assertViewHas('departments');
    }

    #[Test]
    public function guest_cannot_view_departments_page(): void
    {
        $this->get(route('departments.index', ['branch_id' => $this->branch->id]))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function super_admin_can_create_department(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('departments.store'), [
                'branch_id' => $this->branch->id,
                'name' => 'Отдел кадров',
                'code' => 'HR-001',
            ])
            ->assertRedirect(route('departments.index', ['branch_id' => $this->branch->id]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('departments', [
            'branch_id' => $this->branch->id,
            'name' => 'Отдел кадров',
            'code' => 'HR-001',
        ]);
    }

    #[Test]
    public function guest_cannot_create_department(): void
    {
        $this->post(route('departments.store'), [
            'branch_id' => $this->branch->id,
            'name' => 'Отдел кадров',
            'code' => 'HR-001',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('departments', ['code' => 'HR-001']);
    }

    #[Test]
    public function super_admin_can_delete_department(): void
    {
        $department = Department::factory()->create(['branch_id' => $this->branch->id]);

        $this->actingAs($this->superAdmin)
            ->delete(route('departments.destroy', $department))
            ->assertRedirect(route('departments.index', ['branch_id' => $this->branch->id]))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }

    #[Test]
    public function super_admin_cannot_delete_department_with_subdivisions(): void
    {
        $department = Department::factory()->create(['branch_id' => $this->branch->id]);
        $department->subdivisions()->create(['name' => 'Управление', 'code' => 'MGMT-1']);

        $this->actingAs($this->superAdmin)
            ->delete(route('departments.destroy', $department))
            ->assertRedirect(route('departments.index', ['branch_id' => $this->branch->id]))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('departments', ['id' => $department->id]);
    }

    #[Test]
    public function guest_cannot_delete_department(): void
    {
        $department = Department::factory()->create(['branch_id' => $this->branch->id]);

        $this->delete(route('departments.destroy', $department))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('departments', ['id' => $department->id]);
    }
}
