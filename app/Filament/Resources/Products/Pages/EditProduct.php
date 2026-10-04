<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewOnSite')
                ->label('مشاهده در سایت')
                ->icon('heroicon-o-eye')
                ->url(fn ($record) => 'https://sabad.taminfalat.com/#product-'.$record->id)
                ->openUrlInNewTab(),
            ProductResource::guardBundleItemDeletion(DeleteAction::make()),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /** A bundle's products may have changed: its price follows them. */
    protected function afterSave(): void
    {
        if ($this->record->isBundle()) {
            $this->record->syncPriceWithItems();
        }
    }
}
