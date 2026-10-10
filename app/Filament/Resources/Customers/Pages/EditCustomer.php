<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // says how many orders go with the customer, as the list's delete does
            DeleteAction::make()
                ->modalDescription(fn (Customer $record): string => $record->deletionWarning()),
        ];
    }
}
