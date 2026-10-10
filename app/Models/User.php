<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'office',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function userEmployerRoles(): HasMany
    {
        return $this->hasMany(UserEmployerRole::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_employer_roles')
            ->withPivot('employer_id')
            ->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return $this->userEmployerRoles()
            ->whereHas('role', fn ($q) => $q->whereIn('code', ['SUPER_ADMIN', 'ADMIN_TECH']))
            ->exists();
    }

    public function hasRole(string $roleCode, ?int $employerId = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->userEmployerRoles()
            ->whereHas('role', fn ($q) => $q->where('code', $roleCode))
            ->where(function ($q) use ($employerId) {
                $q->whereNull('employer_id');
                if ($employerId !== null) {
                    $q->orWhere('employer_id', $employerId);
                }
            })
            ->exists();
    }

    public function hasPermission(string $permissionCode, ?int $employerId = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->userEmployerRoles()
            ->where(function ($q) use ($employerId) {
                $q->whereNull('employer_id');
                if ($employerId !== null) {
                    $q->orWhere('employer_id', $employerId);
                }
            })
            ->whereHas('role.permissions', fn ($q) => $q->where('code', $permissionCode))
            ->exists();
    }

    public function hasAnyPermission(string $permissionCode): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->userEmployerRoles()
            ->whereHas('role.permissions', fn ($q) => $q->where('code', $permissionCode))
            ->exists();
    }

    /**
     * Get accessible employers for this operator.
     */
    public function accessibleEmployers(): Collection
    {
        if ($this->isSuperAdmin()) {
            return Employer::orderBy('legal_name')->get();
        }

        // Operators with global studio role (employer_id null) can access all active employers
        $hasGlobalOperatorRole = $this->userEmployerRoles()
            ->whereNull('employer_id')
            ->whereHas('role', fn ($q) => $q->whereIn('code', [
                'ADMIN_TECH', 'ADMIN_FUNC', 'LIQUIDADOR', 'REVISOR', 'AUDITOR',
                'ADMIN_ESTUDIO', 'LIQUIDADOR_SENIOR', 'OPERADOR_JUNIOR'
            ]))
            ->exists();

        if ($hasGlobalOperatorRole) {
            return Employer::where('status', 'ACTIVE')->orderBy('legal_name')->get();
        }

        $assignedIds = $this->userEmployerRoles()
            ->whereNotNull('employer_id')
            ->pluck('employer_id')
            ->unique();

        return Employer::whereIn('id', $assignedIds)->orderBy('legal_name')->get();
    }
}
