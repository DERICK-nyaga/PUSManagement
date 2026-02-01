<?php

namespace App\Traits;

trait HasRoles
{
    public function hasRole($role): bool
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }

        return $this->role === $role;
    }

    public function assignRole($role): void
    {
        $this->role = $role;
        $this->save();
    }

    public function removeRole(): void
    {
        $this->role = null;
        $this->save();
    }

    public function isHrManager(): bool
    {
        return $this->hasRole('hr_manager');
    }

    public function isDepartmentHead(): bool
    {
        return $this->hasRole('department_head');
    }

    public function isSupervisor(): bool
    {
        return $this->hasRole('supervisor');
    }
}
