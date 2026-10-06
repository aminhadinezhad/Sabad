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

    /** The owner's account: no other admin can delete it, switch its access off or take its permissions away. */
    public const OWNER_EMAIL = 'mohammadaminhadinezhad@gmail.com';

    protected static function booted(): void
    {
        // anyone else saving the owner's account leaves their access on and their email (which is what
        // marks the account as theirs) as it was
        static::saving(function (User $user) {
            if ($user->exists && $user->isOwner() && ! auth()->user()?->isOwner()) {
                $user->has_access = true;
                $user->email = $user->getOriginal('email');
            }
        });

        // and cannot delete it
        static::deleting(fn (User $user) => ! $user->isOwner() || (bool) auth()->user()?->isOwner());
    }

    public function isOwner(): bool
    {
        return strcasecmp(trim((string) ($this->getOriginal('email') ?? $this->email)), self::OWNER_EMAIL) === 0;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->has_access || $this->isOwner();
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
