<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Models\Product;
use App\Support\PersianDate;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('نام محصول')
                    ->searchable(),

                TextColumn::make('code')
                    ->label('کدینگ')
                    ->searchable(),

                ImageColumn::make('image')
                    ->label('عکس')
                    ->disk('public')
                    ->defaultImageUrl(asset(Product::PLACEHOLDER_IMAGE)),

                TextColumn::make('category')
                    ->label('دسته‌بندی')
                    ->formatStateUsing(fn ($state) => ProductForm::CATEGORIES[$state] ?? $state)
                    ->badge(fn ($state) => $state === Product::BUNDLE_CATEGORY)
                    ->color(fn ($state) => $state === Product::BUNDLE_CATEGORY ? 'warning' : null),

                TextColumn::make('brand.name')
                    ->label('برند'),

                TextColumn::make('price')
                    ->label('قیمت')
                    ->formatStateUsing(fn ($state) => number_format($state)),

                TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->formatStateUsing(fn ($state) => PersianDate::date($state))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->filters([
                Filter::make('name')
                    ->schema([
                        TextInput::make('name')
                            ->label('نام محصول'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['name'] ?? null,
                        fn (Builder $query, $value) => $query->where('name', 'like', "%{$value}%"),
                    )),

                SelectFilter::make('category')
                    ->label('دسته‌بندی')
                    ->placeholder('یک گزینه را انتخاب کنید')
                    ->options(ProductForm::CATEGORIES),

                SelectFilter::make('brand_id')
                    ->label('برند')
                    ->placeholder('یک گزینه را انتخاب کنید')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('code')
                    ->schema([
                        TextInput::make('code')
                            ->label('کدینگ'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['code'] ?? null,
                        fn (Builder $query, $value) => $query->where('code', $value),
                    )),
            ])
            ->deferFilters()
            ->recordActions([
                EditAction::make(),
                ProductResource::guardBundleItemDeletion(DeleteAction::make()),
                Action::make('viewOnSite')
                    ->label('مشاهده در سایت')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Product $record) => 'https://sabad.taminfalat.com/#product-'.$record->id)
                    ->openUrlInNewTab(),
            ]);
    }
}
