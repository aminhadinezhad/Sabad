<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\PermissionRegistrar;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /** The owner's permissions when someone else opened their account, put back after saving. */
    protected ?array $ownerPermissions = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->failureNotificationTitle('این ادمین را نمی توان حذف کرد.'),
        ];
    }

    protected function beforeSave(): void
    {
        if ($this->record->isOwner() && ! auth()->user()?->isOwner()) {
            $this->ownerPermissions = $this->record->permissions()->pluck('id')->all();
        }
    }

    protected function afterSave(): void
    {
        if ($this->ownerPermissions !== null) {
            $this->record->permissions()->sync($this->ownerPermissions);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
}
