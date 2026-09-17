<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Subdivision;
use App\Models\User;
use App\Models\VacancyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StatementVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }
    }

    private function userWithRole(UserRole $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role->value);

        return $user;
    }

    #[Test]
    public function employee_cannot_view_statements_index(): void
    {
        $this->actingAs($this->userWithRole(UserRole::EMPLOYEE))
            ->get(route('statements.index'))
            ->assertForbidden();
    }

    #[Test]
    public function employee_cannot_open_create_form(): void
    {
        $this->actingAs($this->userWithRole(UserRole::EMPLOYEE))
            ->get(route('statements.create'))
            ->assertForbidden();
    }

    #[Test]
    public function department_head_sees_only_own_statements(): void
    {
        $head = $this->userWithRole(UserRole::DEPARTMENT_HEAD);

        VacancyRequest::factory()->count(2)->create([
            'requester_id' => $head->id,
            'status' => 'draft',
        ]);
        VacancyRequest::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($head)
            ->get(route('statements.index'))
            ->assertOk()
            ->assertViewIs('statements.index');

        $this->assertCount(2, $response->viewData('statements'));
    }

    #[Test]
    public function department_head_cannot_view_foreign_statement(): void
    {
        $head = $this->userWithRole(UserRole::DEPARTMENT_HEAD);
        $foreign = VacancyRequest::factory()->create(['status' => 'draft']);

        $this->actingAs($head)
            ->get(route('statements.show', $foreign))
            ->assertForbidden();
    }

    #[Test]
    public function department_head_can_view_create_form(): void
    {
        $subdivision = Subdivision::factory()->create();
        $head = $this->userWithRole(UserRole::DEPARTMENT_HEAD);
        $head->update(['subdivision_id' => $subdivision->id]);

        $this->actingAs($head)
            ->get(route('statements.create'))
            ->assertOk()
            ->assertViewIs('statements.create')
            ->assertViewHas('allPositions')
            ->assertViewHas('vacantPositions');
    }

    #[Test]
    public function hr_manager_cannot_view_draft_statement(): void
    {
        $statement = VacancyRequest::factory()->create(['status' => 'draft']);

        $this->actingAs($this->userWithRole(UserRole::HR_MANAGER))
            ->get(route('statements.show', $statement))
            ->assertForbidden();
    }

    #[Test]
    public function hr_manager_can_view_submitted_statement(): void
    {
        $statement = VacancyRequest::factory()->create(['status' => 'submitted']);

        $this->actingAs($this->userWithRole(UserRole::HR_MANAGER))
            ->get(route('statements.show', $statement))
            ->assertOk()
            ->assertViewIs('statements.show')
            ->assertViewHas('supervisors');
    }

    #[Test]
    public function super_admin_cannot_view_draft_statement(): void
    {
        $statement = VacancyRequest::factory()->create(['status' => 'draft']);

        $this->actingAs($this->userWithRole(UserRole::SUPER_ADMIN))
            ->get(route('statements.show', $statement))
            ->assertForbidden();
    }

    #[Test]
    public function super_admin_can_view_statement_on_review(): void
    {
        $statement = VacancyRequest::factory()->supervisorReview()->create();

        $this->actingAs($this->userWithRole(UserRole::SUPER_ADMIN))
            ->get(route('statements.show', $statement))
            ->assertOk();
    }

    #[Test]
    public function department_head_cannot_approve_statement(): void
    {
        $statement = VacancyRequest::factory()->supervisorReview()->create();

        $this->actingAs($this->userWithRole(UserRole::DEPARTMENT_HEAD))
            ->post(route('statements.approve', $statement), ['comment' => 'ok'])
            ->assertForbidden();
    }

    #[Test]
    public function hr_manager_cannot_approve_statement(): void
    {
        $statement = VacancyRequest::factory()->supervisorReview()->create();

        $this->actingAs($this->userWithRole(UserRole::HR_MANAGER))
            ->post(route('statements.approve', $statement), ['comment' => 'ok'])
            ->assertForbidden();
    }

    #[Test]
    public function hr_manager_can_delete_submitted_statement(): void
    {
        $statement = VacancyRequest::factory()->create(['status' => 'submitted']);

        $this->actingAs($this->userWithRole(UserRole::HR_MANAGER))
            ->delete(route('statements.destroy', $statement))
            ->assertRedirect(route('statements.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('vacancy_requests', ['id' => $statement->id]);
    }

    #[Test]
    public function hr_manager_cannot_delete_approved_statement(): void
    {
        $statement = VacancyRequest::factory()->approved()->create();

        $this->actingAs($this->userWithRole(UserRole::HR_MANAGER))
            ->delete(route('statements.destroy', $statement))
            ->assertForbidden();

        $this->assertDatabaseHas('vacancy_requests', ['id' => $statement->id]);
    }
}
