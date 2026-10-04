<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cube;

    protected static string|UnitEnum|null $navigationGroup = 'فروشگاه آنلاین';

    protected static ?string $modelLabel = 'کالا';

    protected static ?string $pluralModelLabel = 'کالا و محصول';

    protected static ?string $navigationLabel = 'کالا و محصول';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    /**
     * A product in a bundle is not deleted: the bundle's price is made of it. The delete button
     * says which bundles hold it instead.
     */
    public static function guardBundleItemDeletion(DeleteAction $action): DeleteAction
    {
        return $action->before(function (DeleteAction $action, Product $record) {
            $bundles = Product::bundles()
                ->whereHas('bundleItems', fn (Builder $q) => $q->where('product_id', $record->id))
                ->orderBy('name')
                ->pluck('name');

            if ($bundles->isEmpty()) {
                return;
            }

            Notification::make()
                ->title('این کالا حذف نشد')
                ->body('این کالا در '.$bundles->map(fn ($name) => '«'.$name.'»')->implode(' و ').' است. اول آن را از سبد بردارید، بعد حذف کنید.')
                ->danger()
                ->persistent()
                ->send();

            $action->cancel();
        });
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::auth()->user()?->can('access_products') ?? false;
    }

    public static function canViewAny(): bool
    {
        return Filament::auth()->user()?->can('access_products') ?? false;
    }
}
