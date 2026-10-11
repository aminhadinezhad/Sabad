<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The products' ids closed up after a deletion, as on the server: products 1 to 39 with 34 deleted,
 * then three bundles holding products from both sides of the gap.
 */
class CloseProductIdGapsTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, array{price: int, code: int|null, image: string|null}> */
    private array $before = [];

    protected function setUp(): void
    {
        parent::setUp();

        for ($i = 1; $i <= 39; $i++) {
            Product::create(['name' => "کالای $i", 'code' => $i === 34 ? null : ($i <= 33 ? $i : $i - 1), 'category' => 'rice', 'price' => $i * 1000, 'image' => "products/$i.webp"]);
        }
        Product::find(34)->delete();

        $lines = [[1, 2, 35], [36, 37], [10, 38, 39]];
        foreach (['سبد اقتصادی', 'سبد استاندارد', 'سبد ویژه'] as $i => $name) {
            $bundle = Product::create(['name' => $name, 'code' => 1000 + $i, 'category' => Product::BUNDLE_CATEGORY, 'price' => 0, 'unit' => 'سبد']);
            foreach ($lines[$i] as $productId) {
                $bundle->bundleItems()->create(['product_id' => $productId, 'quantity' => 2]);
            }
        }

        foreach (Product::all() as $product) {
            $this->before[$product->name] = ['price' => (int) $product->fresh()->price, 'code' => $product->code, 'image' => $product->image];
        }
    }

    /** @return array<string, list<string>> each bundle's products, by name */
    private function bundleContents(): array
    {
        return Product::bundles()->with('bundleItems.product')->get()
            ->mapWithKeys(fn (Product $bundle) => [$bundle->name => $bundle->bundleItems->map(fn ($line) => $line->product->name.' x'.$line->quantity)->sort()->values()->all()])
            ->all();
    }

    public function test_without_apply_it_only_shows_the_plan(): void
    {
        $this->artisan('products:close-id-gaps')
            ->expectsTable(['id now', 'id after', 'product'], [[35, 34, 'کالای 35'], [36, 35, 'کالای 36'], [37, 36, 'کالای 37'], [38, 37, 'کالای 38'], [39, 38, 'کالای 39'], [40, 39, 'سبد اقتصادی'], [41, 40, 'سبد استاندارد'], [42, 41, 'سبد ویژه']])
            ->expectsOutputToContain('Nothing changed')
            ->assertSuccessful();

        $this->assertNull(Product::find(34));
        $this->assertSame('کالای 35', Product::find(35)->name);
    }

    public function test_with_apply_the_ids_close_up_and_everything_goes_with_its_product(): void
    {
        $contentsBefore = $this->bundleContents();

        $this->artisan('products:close-id-gaps --apply')->expectsOutputToContain('8 products renumbered')->assertSuccessful();

        // the products sold on their own now run 1 to 38 with no gap
        $this->assertSame(range(1, 38), Product::singles()->orderBy('id')->pluck('id')->all());
        $this->assertSame('کالای 35', Product::find(34)->name);
        $this->assertSame('کالای 39', Product::find(38)->name);
        // the bundles follow straight after, in the same order
        $this->assertSame([39, 40, 41], Product::bundles()->orderBy('id')->pluck('id')->all());
        $this->assertSame(['سبد اقتصادی', 'سبد استاندارد', 'سبد ویژه'], Product::bundles()->orderBy('id')->pluck('name')->all());
        // each product kept its price, code and picture; each bundle its products and price
        foreach (Product::all() as $product) {
            $this->assertSame($this->before[$product->name], ['price' => (int) $product->price, 'code' => $product->code, 'image' => $product->image], $product->name);
        }
        $this->assertSame($contentsBefore, $this->bundleContents());
        $this->assertSame([], DB::select('PRAGMA foreign_key_check'));

        // a second run finds nothing to do, and a new product follows the last id
        $this->artisan('products:close-id-gaps --apply')->expectsOutputToContain('No gaps')->assertSuccessful();
        $this->assertGreaterThan(42, Product::create(['name' => 'کالای تازه', 'category' => 'rice', 'price' => 1])->id);
    }

    public function test_as_on_the_server_now_only_the_bundles_move_down_one(): void
    {
        // the products already closed up (1 to 38), the bundles still at 40 to 42
        $this->artisan('products:close-id-gaps --apply')->assertSuccessful();
        foreach ([41, 40, 39] as $id) {
            DB::transaction(function () use ($id) {
                DB::statement('PRAGMA defer_foreign_keys = ON');
                DB::table('products')->where('id', $id)->update(['id' => $id + 1]);
                DB::table('bundle_items')->where('bundle_id', $id)->update(['bundle_id' => $id + 1]);
            });
        }
        $this->assertSame([40, 41, 42], Product::bundles()->orderBy('id')->pluck('id')->all());
        $contentsBefore = $this->bundleContents();
        $pricesBefore = Product::bundles()->pluck('price', 'name')->all();

        $this->artisan('products:close-id-gaps')
            ->expectsTable(['id now', 'id after', 'product'], [[40, 39, 'سبد اقتصادی'], [41, 40, 'سبد استاندارد'], [42, 41, 'سبد ویژه']])
            ->assertSuccessful();
        $this->artisan('products:close-id-gaps --apply')->expectsOutputToContain('3 products renumbered')->assertSuccessful();

        $this->assertSame(range(1, 38), Product::singles()->orderBy('id')->pluck('id')->all());
        $this->assertSame([39, 40, 41], Product::bundles()->orderBy('id')->pluck('id')->all());
        $this->assertSame($contentsBefore, $this->bundleContents());
        $this->assertSame($pricesBefore, Product::bundles()->pluck('price', 'name')->all());
        $this->assertSame([], DB::select('PRAGMA foreign_key_check'));
    }

    public function test_the_price_import_then_finds_every_product_by_its_own_number(): void
    {
        $this->artisan('products:close-id-gaps --apply')->assertSuccessful();

        // a list numbered 1 to 38 now lines up with the ids, row for row
        foreach (range(1, 38) as $n) {
            $this->assertSame('کالای '.($n < 34 ? $n : $n + 1), Product::find($n)->name);
        }
    }
}
