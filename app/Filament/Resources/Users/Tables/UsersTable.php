<?php

namespace App\Filament\Resources\Users\Tables;

use App\Support\PersianDate;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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
            ->recordActions([
                EditAction::make(),

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

                DeleteAction::make(),
            ]);
    }
}
