<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'مدیریت سیستم';

    protected static ?string $modelLabel = 'ادمین';

    protected static ?string $pluralModelLabel = 'ادمین‌ ها';

    protected static ?string $navigationLabel = 'ادمین‌ ها';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::auth()->user()?->can('access_admins') ?? false;
    }

    public static function canViewAny(): bool
    {
        return Filament::auth()->user()?->can('access_admins') ?? false;
    }

    public static function canCreate(): bool
    {
        return Filament::auth()->user()?->can('access_admins') ?? false;
    }

    /** A super admin's account is edited by no one but themselves. */
    public static function canEdit($record): bool
    {
        return $record->isManageableBy(Filament::auth()->user());
    }

    /** No one deletes themselves, and no one deletes a super admin. */
    /**
     * The delete button shows on every row, so a super admin's row looks like the rest; what may
     * not be deleted is refused with a message when it is clicked (guardDeletion), and the model
     * itself never lets a super admin go.
     */
    public static function canDelete($record): bool
    {
        return Filament::auth()->user()?->can('access_admins') ?? false;
    }

    /** Why $record may not be deleted by whoever is signed in, or null when it may. */
    public static function deletionRefusal(User $record): ?string
    {
        return match (true) {
            $record->is(Filament::auth()->user()) => 'حساب خودتان را نمی توانید حذف کنید.', // خودشو نتونه حذف کنه
            (bool) $record->is_super_admin => 'این ادمین را نمی توان حذف کرد.',
            default => null,
        };
    }

    /** A delete button that asks for confirmation only when the deletion may go ahead. */
    public static function guardDeletion(DeleteAction $action): DeleteAction
    {
        return $action
            // a refused deletion opens no confirmation box: the click goes straight to the message
            ->modalHidden(fn (User $record): bool => static::deletionRefusal($record) !== null)
            ->before(function (DeleteAction $action, User $record): void {
                if ($why = static::deletionRefusal($record)) {
                    Notification::make()->title($why)->danger()->send();
                    $action->cancel();
                }
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
