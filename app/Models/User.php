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

    /** The owner's account: only its owner can save changes to it, reset its password or delete it. */
    public const OWNER_EMAIL = 'mohammadaminhadinezhad@gmail.com';

    /** Whether $actor is someone other than the owner trying to change the owner's account. */
    public function isOwnerAccountFor(?User $actor): bool
    {
        $isOwner = fn (?User $user) => $user && strcasecmp(trim((string) $user->getOriginal('email')), self::OWNER_EMAIL) === 0;

        return $isOwner($this) && ! $isOwner($actor);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->has_access;
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
        ];
    }
}
