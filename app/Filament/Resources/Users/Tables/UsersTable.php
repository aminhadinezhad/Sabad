<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Support\PersianDate;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('نام ادمین')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('پست الکترونیک')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('شماره موبایل')
                    ->searchable(),

                IconColumn::make('has_access')
                    ->label('دسترسی دارد؟')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->formatStateUsing(fn ($state) => PersianDate::date($state))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // a super admin's row shows no actions to anyone else (UserResource also refuses them)
            // Every row shows the same buttons. On a super admin's row, for anyone but themselves,
            // edit and password reset are look-alikes that only say it cannot be done.
            ->recordActions([
                EditAction::make()
                    ->visible(fn (User $record) => $record->isManageableBy(Filament::auth()->user())),

                Action::make('editLocked')
                    ->label(__('filament-actions::edit.single.label'))
                    ->tableIcon(Heroicon::PencilSquare)
                    ->color('primary')
                    ->visible(fn (User $record) => ! $record->isManageableBy(Filament::auth()->user()))
                    ->action(fn () => Notification::make()->title('این ادمین را نمی توان ویرایش کرد.')->danger()->send()),

                Action::make('resetPassword')
                    ->label('پسورد ریست')
                    ->icon('heroicon-o-key')
                    ->modalHeading('پسورد ریست')
                    ->modalWidth('sm')
                    ->schema([
                        TextInput::make('password')
                            ->label('رمز عبور')
                            ->password()
                            ->required()
                            ->minLength(8),

                        TextInput::make('password_confirmation')
                            ->label('تکرار رمز عبور')
                            ->password()
                            ->required()
                            ->same('password'),
                    ])
                    ->visible(fn (User $record) => $record->isManageableBy(Filament::auth()->user()))
                    ->action(function (array $data, $record) {
                        $record->update([
                            'password' => Hash::make($data['password']),
                        ]);

                        Notification::make()
                            ->title('رمز عبور با موفقیت تغییر کرد')
                            ->success()
                            ->send();
                    })
                    ->modalSubmitActionLabel('ثبت'),

                // a super admin's password is theirs alone
                Action::make('resetPasswordLocked')
                    ->label('پسورد ریست')
                    ->icon('heroicon-o-key')
                    ->visible(fn (User $record) => ! $record->isManageableBy(Filament::auth()->user()))
                    ->action(fn () => Notification::make()->title('رمز عبور این ادمین را نمی توان تغییر داد.')->danger()->send()),

                UserResource::guardDeletion(DeleteAction::make()),
            ]);
    }
}
