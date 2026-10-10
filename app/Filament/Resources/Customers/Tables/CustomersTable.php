<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use App\Support\PersianDate;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('نام')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('موبایل')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => PersianDate::date($state))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('ویرایش'),

                DeleteAction::make()
                    ->label('حذف')
                    ->requiresConfirmation()
                    ->modalHeading(
                        fn (Customer $record): string => "حذف {$record->full_name}"
                    )
                    ->modalDescription(fn (Customer $record): string => $record->deletionWarning())
                    ->modalSubmitActionLabel('حذف')
                    ->modalCancelActionLabel('لغو'),
            ])
            ->recordUrl(
                fn ($record) => CustomerResource::getUrl('edit', ['record' => $record]),
            );
    }
}
