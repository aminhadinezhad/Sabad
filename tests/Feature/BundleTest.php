<?php

namespace Tests\Feature;

use App\Filament\Pages\OnlineStoreRoutines;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\BundleItem;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * «سبد اختصاصی»: a product made of other products, sold as one line with its own code, whose
 * price is always the sum of theirs.
 */
class BundleTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name, int $price, array $overrides = []): Product
    {
        return Product::create(array_merge(['name' => $name, 'category' => 'rice', 'price' => $price], $overrides));
    }

    /**
     * @param  list<array{0: Product, 1: int}>  $lines  [product, quantity]
     */
    private function bundle(string $name, array $lines, array $overrides = []): Product
    {
        $bundle = Product::create(array_merge(['name' => $name, 'category' => Product::BUNDLE_CATEGORY, 'price' => 0, 'unit' => 'سبد'], $overrides));

        foreach ($lines as [$product, $quantity]) {
            $bundle->bundleItems()->create(['product_id' => $product->id, 'quantity' => $quantity]);
        }

        $bundle->syncPriceWithItems();

        return $bundle->fresh();
    }

    private function admin(string ...$permissions): User
    {
        Filament::setCurrentPanel('admin');
        $user = User::factory()->create(['has_access' => true]);

        foreach ($permissions as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        return $user;
    }

    /**
     * @param  list<list<mixed>>  $rows  the rows under the headings
     */
    private function excel(array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'test').'.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(OnlineStoreRoutines::HEADINGS));
        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }
        $writer->close();

        return UploadedFile::fake()->createWithContent('prices.xlsx', file_get_contents($path));
    }

    /** One card's HTML on the home page: from its id to the next card, or the end of its section. */
    private function cardHtml(string $html, Product $product): string
    {
        $card = substr($html, strpos($html, 'id="product-'.$product->id.'"') + 1);
        $ends = array_filter([strpos($card, 'id="product-'), strpos($card, '</section>')], fn ($at) => $at !== false);

        return substr($card, 0, $ends ? min($ends) : strlen($card));
    }

    public function test_a_bundle_is_made_in_the_products_form_and_priced_from_its_products(): void
    {
        Repeater::fake();
        $this->actingAs($this->admin('access_products'));
        $rice = $this->product('برنج هاشمی', 150000);
        $oil = $this->product('روغن مایع', 90000, ['category' => 'groceries']);

        Livewire::withQueryParams(['category' => Product::BUNDLE_CATEGORY])
            ->test(CreateProduct::class)
            ->assertFormSet(['category' => Product::BUNDLE_CATEGORY])
            ->assertFormFieldHidden('brand_id')
            ->assertFormFieldHidden('unit')
            ->assertFormFieldHidden('price')
            ->assertFormFieldHidden('vat_enabled')
            ->assertFormFieldVisible('bundleItems')
            ->fillForm([
                'name' => 'سبد اقتصادی',
                'code' => 500,
                'bundleItems' => [
                    ['product_id' => $rice->id, 'quantity' => 2],
                    ['product_id' => $oil->id, 'quantity' => 1],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $bundle = Product::where('name', 'سبد اقتصادی')->sole();
        $this->assertTrue($bundle->isBundle());
        $this->assertSame(2 * 150000 + 90000, (int) $bundle->price);
        $this->assertSame(500, (int) $bundle->code);
        $this->assertSame('سبد', $bundle->unit);
        $this->assertNull($bundle->brand_id);
        $this->assertSame([2, 1], $bundle->bundleItems()->orderBy('id')->pluck('quantity')->all());
    }

    public function test_a_product_form_is_unchanged_and_cannot_become_a_bundle(): void
    {
        $this->actingAs($this->admin('access_products'));
        $rice = $this->product('برنج هاشمی', 150000);

        Livewire::test(CreateProduct::class)
            ->assertFormFieldVisible('brand_id')
            ->assertFormFieldVisible('price')
            ->assertFormFieldHidden('bundleItems');

        // «سبد اختصاصی» is not among a product's categories
        Livewire::test(EditProduct::class, ['record' => $rice->getRouteKey()])
            ->assertFormFieldHidden('bundleItems')
            ->assertFormFieldEnabled('category')
            ->fillForm(['category' => Product::BUNDLE_CATEGORY])
            ->call('save')
            ->assertHasFormErrors(['category']);

        $this->assertSame('rice', $rice->fresh()->category);
    }

    public function test_a_bundle_needs_at_least_one_product(): void
    {
        Repeater::fake();
        $this->actingAs($this->admin('access_products'));

        Livewire::withQueryParams(['category' => Product::BUNDLE_CATEGORY])
            ->test(CreateProduct::class)
            ->fillForm(['name' => 'سبد خالی', 'code' => 501, 'bundleItems' => []])
            ->call('create')
            ->assertHasFormErrors(['bundleItems']);

        $this->assertSame(0, Product::bundles()->count());
    }

    public function test_changing_a_bundles_products_reprices_it(): void
    {
        Repeater::fake();
        $this->actingAs($this->admin('access_products'));
        $rice = $this->product('برنج هاشمی', 150000);
        $tea = $this->product('چای', 40000, ['category' => 'groceries']);
        $bundle = $this->bundle('سبد استاندارد', [[$rice, 1]], ['code' => 502]);

        Livewire::test(EditProduct::class, ['record' => $bundle->getRouteKey()])
            ->assertFormFieldDisabled('category')
            ->fillForm(['bundleItems' => [
                ['product_id' => $rice->id, 'quantity' => 3],
                ['product_id' => $tea->id, 'quantity' => 2],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(3 * 150000 + 2 * 40000, (int) $bundle->fresh()->price);
        $this->assertSame(Product::BUNDLE_CATEGORY, $bundle->fresh()->category);
    }

    public function test_a_products_new_price_reaches_the_bundles_it_is_in_and_no_others(): void
    {
        $rice = $this->product('برنج هاشمی', 150000);
        $oil = $this->product('روغن مایع', 90000);
        $withRice = $this->bundle('سبد ویژه', [[$rice, 2], [$oil, 1]]);
        $withoutRice = $this->bundle('سبد اقتصادی', [[$oil, 3]]);

        $rice->update(['price' => 160000]);

        $this->assertSame(2 * 160000 + 90000, (int) $withRice->fresh()->price);
        $this->assertSame(3 * 90000, (int) $withoutRice->fresh()->price);
    }

    public function test_importing_prices_reprices_the_bundles_and_says_so(): void
    {
        $this->actingAs($this->admin('access_routines'));
        $rice = $this->product('برنج هاشمی', 150000);
        $oil = $this->product('روغن مایع', 90000);
        $bundle = $this->bundle('سبد ویژه', [[$rice, 2], [$oil, 1]]);

        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', $this->excel([
                [$rice->id, 'ندارد', 'برنج هاشمی', 1750000],
                [$oil->id, 'ندارد', 'روغن مایع', 900000],
            ]))
            ->call('importPrices')
            ->assertHasNoErrors()
            ->assertSet('importErrors', [])
            ->assertNotified(
                Notification::make()
                    ->title('قیمت‌ها بروزرسانی شد')
                    ->body('قیمت ۱ کالا تغییر کرد و ۱ کالا بدون تغییر ماند. قیمت ۱ سبد اختصاصی هم بر اساس کالاهایش به روز شد.')
                    ->success()
                    // (Filament keeps every import message on screen until closed)
                    ->persistent()
            );

        $this->assertSame(2 * 175000 + 90000, (int) $bundle->fresh()->price);
    }

    public function test_bundles_are_left_out_of_the_price_file_and_skipped_if_put_back_in(): void
    {
        $this->actingAs($this->admin('access_routines'));
        $rice = $this->product('برنج هاشمی', 150000);
        $bundle = $this->bundle('سبد ویژه', [[$rice, 2]]);

        $download = Livewire::test(OnlineStoreRoutines::class)->call('exportPrices')->effects['download'];
        $path = tempnam(sys_get_temp_dir(), 'dl').'.xlsx';
        file_put_contents($path, base64_decode($download['content']));
        $reader = new Reader;
        $reader->open($path);
        $ids = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $ids[] = $row->toArray()[0];
            }
        }
        $reader->close();
        $this->assertSame([OnlineStoreRoutines::HEADINGS[0], $rice->id], $ids);

        // a bundle row typed in by hand, even one whose lookup failed, neither prices the bundle
        // nor stops the file
        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', $this->excel([
                [$rice->id, 'ندارد', 'برنج هاشمی', 1600000],
                [$bundle->id, 'ندارد', 'سبد ویژه', '#N/A'],
            ]))
            ->call('importPrices')
            ->assertSet('importErrors', [])
            ->assertNotified(
                Notification::make()
                    ->title('قیمت‌ها بروزرسانی شد')
                    ->body('قیمت ۱ کالا تغییر کرد و ۰ کالا بدون تغییر ماند. قیمت ۱ سبد اختصاصی هم بر اساس کالاهایش به روز شد. ۱ سطر مربوط به سبد اختصاصی بود و نادیده گرفته شد؛ قیمت سبدها از کالاهایشان حساب می شود (سطر ۳).')
                    ->success()
                    ->persistent()
            );

        $this->assertSame(2 * 160000, (int) $bundle->fresh()->price);
    }

    public function test_a_bundles_vat_comes_to_its_products_vat_bought_one_by_one(): void
    {
        $rice = $this->product('برنج هاشمی', 100000, ['vat_enabled' => true, 'vat_percentage' => 10]);
        $beans = $this->product('لوبیا', 50000, ['category' => 'legumes']);
        // switched off VAT counts as none, whatever percentage is left in the field
        $salt = $this->product('نمک', 10000, ['category' => 'groceries', 'vat_enabled' => false, 'vat_percentage' => 10]);
        $bundle = $this->bundle('سبد ویژه', [[$rice, 2], [$beans, 1], [$salt, 1]]);

        $this->assertSame(260000, (int) $bundle->price);
        // 20,000 VAT on 260,000
        $this->assertEqualsWithDelta(20000 / 260000 * 100, $bundle->vatPercent(), 1e-12);
        $this->assertSame(20000.0, round(260000 * $bundle->vatPercent() / 100, 6));

        $html = $this->get(route('products.index'))->assertOk()->getContent();
        $this->assertStringContainsString('data-vat-percent="'.round(20000 / 260000 * 100, 10).'"', $this->cardHtml($html, $bundle));
    }

    public function test_the_site_shows_bundles_cheapest_first_as_one_product_each(): void
    {
        $rice = $this->product('برنج هاشمی', 150000);
        $oil = $this->product('روغن مایع', 90000, ['category' => 'groceries']);
        $tea = $this->product('چای', 40000, ['category' => 'groceries']);
        $special = $this->bundle('سبد ویژه', [[$rice, 2], [$oil, 1], [$tea, 1]], ['code' => 702]);
        $economy = $this->bundle('سبد اقتصادی', [[$rice, 1]], ['code' => 700, 'description' => 'شامل برنج و دیگر اقلام پایه']);

        $html = $this->get(route('products.index'))->assertOk()->getContent();

        $this->assertLessThan(strpos($html, 'id="product-'.$special->id.'"'), strpos($html, 'id="product-'.$economy->id.'"'));

        $card = $this->cardHtml($html, $special);
        $this->assertStringContainsString('bundle-card', $card);
        $this->assertStringContainsString('data-code="702"', $card);
        $this->assertStringContainsString('data-price="430000"', $card);
        $this->assertStringContainsString('430,000', $card);
        $this->assertStringContainsString('شامل ۲ × برنج هاشمی، روغن مایع و چای', $card);
        $this->assertStringContainsString('data-qty-control', $card);
        $this->assertStringContainsString('شامل برنج و دیگر اقلام پایه', $this->cardHtml($html, $economy));

        // a bundle is in no category, and each shows once
        $this->assertSame(1, substr_count($html, 'id="product-'.$special->id.'"'));
    }

    public function test_no_bundles_no_section(): void
    {
        $this->product('برنج هاشمی', 150000);

        $this->get(route('products.index'))->assertOk()->assertDontSee('class="deals__rail"', false);
    }

    public function test_a_bundle_is_out_of_stock_when_any_of_its_products_is(): void
    {
        $rice = $this->product('برنج هاشمی', 150000);
        $oil = $this->product('روغن مایع', 90000, ['category' => 'groceries', 'is_available' => false]);
        $bundle = $this->bundle('سبد ویژه', [[$rice, 1], [$oil, 1]]);

        $this->assertFalse($bundle->isOrderable());

        $card = $this->cardHtml($this->get(route('products.index'))->getContent(), $bundle);
        $this->assertStringContainsString('product-card__unavailable">ناموجود', $card);
        $this->assertStringNotContainsString('data-qty-control', $card);

        $oil->update(['is_available' => true]);
        $this->assertTrue($bundle->fresh()->isOrderable());
    }

    public function test_a_product_in_a_bundle_is_not_deleted_but_a_bundle_is(): void
    {
        $this->actingAs($this->admin('access_products'));
        $rice = $this->product('برنج هاشمی', 150000);
        $loose = $this->product('عدس', 70000, ['category' => 'legumes']);
        $bundle = $this->bundle('سبد ویژه', [[$rice, 1]]);

        Livewire::test(ListProducts::class)
            ->callTableAction('delete', $rice)
            ->assertNotified('این کالا حذف نشد');
        $this->assertNotNull($rice->fresh());

        Livewire::test(ListProducts::class)->callTableAction('delete', $loose);
        $this->assertNull($loose->fresh());

        Livewire::test(ListProducts::class)->callTableAction('delete', $bundle);
        $this->assertNull($bundle->fresh());
        $this->assertSame(0, BundleItem::count());
        $this->assertNotNull($rice->fresh());
    }
}
