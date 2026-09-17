<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_code',
        'branch_id',
        'department_id',
        'subdivision_id',
        'position_id',
        'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ─── Отношения ───────────────────────────────────────────────
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function subdivision()
    {
        return $this->belongsTo(Subdivision::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    // ─── Хелперы ────────────────────────────────────────────────
    public function hasUserRole(UserRole $role): bool
    {
        return $this->hasRole($role->value);
    }

    public function hasAnyUserRole(UserRole ...$roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasUserRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function getPositionCategoryAttribute(): ?string
    {
        return $this->position?->category;
    }

    public function getPositionGradeAttribute(): ?int
    {
        return $this->position?->grade;
    }

    public function getDisplayRoleAttribute(): string
    {
        return $this->roles->first()?->name ?? 'Нет роли';
    }
}
