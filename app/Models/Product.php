<?php

namespace App\Models;

use App\Helpers\PersianHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public const PLACEHOLDER_IMAGE = 'assets/images/product-placeholder.svg';

    /**
     * The category of a «سبد اختصاصی»: a product made of other products, sold as one line with its
     * own code. Its price is the sum of theirs and follows them (see refreshBundlePrices()).
     */
    public const BUNDLE_CATEGORY = 'bundle';

    protected $fillable = [
        'name',
        'code',
        'category',
        'brand_id',
        'unit',
        'price',
        'is_available',
        'image',
        'vat_enabled',
        'vat_percentage',
        'description',
    ];

    protected $casts = [
        'vat_enabled' => 'boolean',
        'is_available' => 'boolean',
    ];

    protected static function booted(): void
    {
        // a product's new price reaches the bundles it is in at once, however it changed: the
        // Excel import, a form, anything else
        static::saved(function (Product $product) {
            if (! $product->isBundle() && $product->wasChanged('price')) {
                static::refreshBundlePrices([$product->id]);
            }
        });
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /** A bundle's lines: its products and how many of each. */
    public function bundleItems()
    {
        return $this->hasMany(BundleItem::class, 'bundle_id');
    }

    /** The bundle lines this product appears in. */
    public function inBundles()
    {
        return $this->hasMany(BundleItem::class, 'product_id');
    }

    public function scopeBundles(Builder $query): void
    {
        $query->where('category', self::BUNDLE_CATEGORY);
    }

    /** Products sold on their own, everything but the bundles (a product with no category included). */
    public function scopeSingles(Builder $query): void
    {
        $query->where(fn (Builder $q) => $q->whereNull('category')->orWhere('category', '!=', self::BUNDLE_CATEGORY));
    }

    public function isBundle(): bool
    {
        return $this->category === self::BUNDLE_CATEGORY;
    }

    /**
     * Sets each bundle's price to the sum of its products' prices; only the bundles holding one of
     * $productIds, or all of them. Returns how many bundles changed price.
     *
     * @param  list<int>|null  $productIds
     */
    public static function refreshBundlePrices(?array $productIds = null): int
    {
        $bundles = static::query()
            ->bundles()
            ->when($productIds !== null, fn (Builder $q) => $q->whereHas('bundleItems', fn (Builder $q) => $q->whereIn('product_id', $productIds)))
            ->get();

        return $bundles->filter(fn (Product $bundle) => $bundle->syncPriceWithItems())->count();
    }

    /**
     * Sets this bundle's price to the sum of its products' prices; true when it changed.
     */
    public function syncPriceWithItems(): bool
    {
        $this->load('bundleItems.product');
        $price = $this->bundleItems->sum(fn (BundleItem $item) => $item->quantity * (int) $item->product->price);

        if ((int) $this->price === $price) {
            return false;
        }

        $this->price = $price;
        $this->save();

        return true;
    }

    /**
     * Whether it can be ordered: switched on, and for a bundle, every product in it switched on too.
     */
    public function isOrderable(): bool
    {
        if (! $this->is_available) {
            return false;
        }

        return ! $this->isBundle() || $this->bundleItems->every(fn (BundleItem $item) => $item->product->is_available);
    }

    /**
     * The VAT the cart adds, as a percentage of the price. A bundle's is the share of its products'
     * VAT in its price, unrounded, so the VAT on a bundle comes to the same as on its products
     * bought one by one, whatever their rates.
     */
    public function vatPercent(): float
    {
        if (! $this->isBundle()) {
            return $this->vat_enabled ? (float) $this->vat_percentage : 0.0;
        }

        $price = $this->bundleItems->sum(fn (BundleItem $item) => $item->quantity * (int) $item->product->price);
        $vat = $this->bundleItems->sum(fn (BundleItem $item) => $item->quantity * (int) $item->product->price * $item->product->vatPercent());

        return $price > 0 ? $vat / $price : 0.0;
    }

    /**
     * A bundle's line under its name: the description written for it, or else what it holds,
     * such as «شامل برنج هاشمی، ۲ × روغن مایع و چای».
     */
    public function bundleSummary(): string
    {
        if (filled($this->description)) {
            return trim($this->description);
        }

        $names = $this->bundleItems
            ->map(fn (BundleItem $item) => $item->quantity > 1
                ? PersianHelper::toPersianDigits($item->quantity).' × '.$item->product->name
                : $item->product->name)
            ->values();

        if ($names->isEmpty()) {
            return '';
        }

        $last = $names->pop();

        return 'شامل '.($names->isEmpty() ? $last : $names->implode('، ').' و '.$last);
    }

    /**
     * Public URL of the product image, or the default placeholder when none is uploaded.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): string => $this->image
            ? asset('storage/'.$this->image)
            : asset(self::PLACEHOLDER_IMAGE));
    }
}
