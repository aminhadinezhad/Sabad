<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // the same form, opened as a bundle
            Action::make('createBundle')
                ->label('سبد اختصاصی جدید')
                ->icon('heroicon-o-shopping-bag')
                ->color('gray')
                ->url(fn () => ProductResource::getUrl('create', ['category' => Product::BUNDLE_CATEGORY])),
            CreateAction::make()
                ->label('کالا یا محصول جدید'),
        ];
    }
}
