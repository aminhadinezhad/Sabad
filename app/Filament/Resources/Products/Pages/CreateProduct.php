<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /** A bundle is sold by the «سبد»; its price is set from its products once they are saved. */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['category'] ?? null) === Product::BUNDLE_CATEGORY) {
            $data['unit'] = 'سبد';
            $data['price'] = 0;
            $data['vat_enabled'] = false;
            $data['vat_percentage'] = 0;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->isBundle()) {
            $this->record->syncPriceWithItems();
        }
    }
}
