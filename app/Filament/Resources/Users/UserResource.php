<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Facades\Filament;
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
    public static function canDelete($record): bool
    {
        $currentUser = Filament::auth()->user();

        return $record->isManageableBy($currentUser)
            && $currentUser->id !== $record->id; // خودشو نتونه حذف کنه
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
