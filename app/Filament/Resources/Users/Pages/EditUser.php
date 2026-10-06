<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Tables\UsersTable;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            UsersTable::guardOwnerDeletion(DeleteAction::make()),
        ];
    }

    /** The owner's account is saved by its owner alone: anyone else gets an error and nothing changes. */
    protected function beforeSave(): void
    {
        if ($this->record->isOwnerAccountFor(auth()->user())) {
            Notification::make()->title('امکان ذخیره تغییرات این ادمین وجود ندارد.')->danger()->send();
            $this->halt();
        }
    }
}
