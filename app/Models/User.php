<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'password', 'has_access', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, HasRoles, Notifiable;

    /** A super admin always gets in, whatever their access switch says. */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->has_access || $this->is_super_admin;
    }

    /**
     * Whether $admin may change this account (edit it, reset its password, delete it). A super
     * admin's account is theirs alone; the rest follow the admins permission.
     */
    public function isManageableBy(?User $admin): bool
    {
        if (! $admin?->can('access_admins')) {
            return false;
        }

        return ! $this->is_super_admin || $admin->is($this);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar
            ? asset('storage/'.$this->avatar)
            : asset('images/users/no-image.png');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'has_access' => 'boolean',
            'is_super_admin' => 'boolean',
        ];
    }
}
