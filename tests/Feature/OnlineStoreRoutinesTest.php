<?php

namespace Tests\Feature;

use App\Filament\Pages\OnlineStoreRoutines;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OnlineStoreRoutinesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
        // the access_routines permission itself comes from its migration
    }

    private function admin(bool $canUseRoutines = true): User
    {
        $user = User::factory()->create(['has_access' => true]);

        if ($canUseRoutines) {
            $user->givePermissionTo('access_routines');
        }

        return $user;
    }

    private function product(string $name, int $price): Product
    {
        return Product::create(['name' => $name, 'category' => 'rice', 'price' => $price]);
    }

    /**
     * @param  list<list<mixed>>  $rows  سطرهای بعد از عنوان
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

    public function test_page_is_reachable_only_with_routines_permission(): void
    {
        $this->actingAs($this->admin())->get('/admin/routines')
            ->assertOk()
            ->assertSee('بروز رسانی قیمت کالا - بالک');

        $this->actingAs($this->admin(canUseRoutines: false))->get('/admin/routines')
            ->assertForbidden();
    }

    public function test_routines_permission_is_a_tickable_option_in_the_admins_form(): void
    {
        $admin = User::factory()->create(['has_access' => true]);
        $admin->givePermissionTo(Permission::findOrCreate('access_admins', 'web'));

        $this->actingAs($admin)->get('/admin/users/create')
            ->assertOk()
            ->assertSee('دسترسی به روتین‌های فروشگاه آنلاین');
    }

    public function test_export_lists_every_product_with_its_price(): void
    {
        $this->actingAs($this->admin());
        $rice = $this->product('برنج هاشمی', 150000);
        $beans = $this->product('لوبیا قرمز', 90000);

        $response = Livewire::test(OnlineStoreRoutines::class)->call('exportPrices');
        $response->assertFileDownloaded();

        $file = $response->effects['download'];
        $path = tempnam(sys_get_temp_dir(), 'dl').'.xlsx';
        file_put_contents($path, base64_decode($file['content']));

        $reader = new Reader;
        $reader->open($path);
        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
        }
        $reader->close();

        $this->assertSame(OnlineStoreRoutines::HEADINGS, $rows[0]);
        // in rials, the unit of the commerce team's price list
        $this->assertEquals([$rice->id, 'ندارد', 'برنج هاشمی', 1500000], $rows[1]);
        $this->assertEquals([$beans->id, 'ندارد', 'لوبیا قرمز', 900000], $rows[2]);
    }

    public function test_import_updates_prices_from_fourth_column(): void
    {
        $this->actingAs($this->admin());
        $rice = $this->product('برنج هاشمی', 150000);
        $beans = $this->product('لوبیا قرمز', 90000);
        $lentils = $this->product('عدس', 70000);

        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', $this->excel([
                [$rice->id, 'ندارد', 'برنج هاشمی', 1750000],
                [$beans->id, 'ندارد', 'لوبیا قرمز', '۹۵۰,۰۰۰'],
                [$lentils->id, 'ندارد', 'عدس', 700000],
                ['', '', '', ''],
            ]))
            ->call('importPrices')
            ->assertHasNoErrors()
            ->assertSet('importErrors', [])
            ->assertNotified('قیمت‌ها بروزرسانی شد');

        $this->assertSame(175000, (int) $rice->fresh()->price);
        $this->assertSame(95000, (int) $beans->fresh()->price);
        $this->assertSame(70000, (int) $lentils->fresh()->price);
    }

    public function test_a_single_bad_row_changes_nothing(): void
    {
        $this->actingAs($this->admin());
        $rice = $this->product('برنج هاشمی', 150000);
        $beans = $this->product('لوبیا قرمز', 90000);

        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', $this->excel([
                [$rice->id, 'ندارد', 'برنج هاشمی', 1750000],
                [$beans->id, 'ندارد', 'لوبیا قرمز', 'نامشخص'],
                [99999, 'ندارد', 'کالای ناموجود', 1000],
                [$rice->id, 'ندارد', 'برنج هاشمی', 1800000],
                [$beans->id, 'ندارد', 'لوبیا قرمز', 0],
            ]))
            ->call('importPrices')
            ->assertSet('importErrors', [
                'سطر ۳: قیمت در ستون چهارم باید یک عدد صحیح بیشتر از صفر باشد.',
                'سطر ۵: این کالا در فایل تکراری است.',
                'سطر ۶: قیمت در ستون چهارم باید یک عدد صحیح بیشتر از صفر باشد.',
            ])
            ->assertNotified('هیچ قیمتی تغییر نکرد');

        $this->assertSame(150000, (int) $rice->fresh()->price);
        $this->assertSame(90000, (int) $beans->fresh()->price);
    }

    public function test_rows_of_deleted_products_are_skipped_like_the_main_site(): void
    {
        $this->actingAs($this->admin());
        $rice = $this->product('برنج هاشمی', 150000);
        $removed = $this->product('کالای حذف‌شده', 50000);
        $removedId = $removed->id;
        $removed->delete();

        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', $this->excel([
                [$rice->id, 'ندارد', 'برنج هاشمی', 1750000],
                [$removedId, 'ندارد', 'کالای حذف‌شده', 600000],
            ]))
            ->call('importPrices')
            ->assertSet('importErrors', [])
            ->assertNotified(
                Notification::make()
                    ->title('قیمت‌ها بروزرسانی شد')
                    ->body('قیمت ۱ کالا تغییر کرد و ۰ کالا بدون تغییر ماند. ۱ سطر مربوط به کالای حذف‌شده بود و نادیده گرفته شد (سطر ۳).')
                    ->success()
                    ->persistent()
            );

        $this->assertSame(175000, (int) $rice->fresh()->price);
        $this->assertNull(Product::find($removedId));
    }

    public function test_file_with_only_deleted_products_changes_nothing(): void
    {
        $this->actingAs($this->admin());
        $rice = $this->product('برنج هاشمی', 150000);

        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', $this->excel([[99999, 'ندارد', 'کالای ناموجود', 1000]]))
            ->call('importPrices')
            ->assertSet('importErrors', ['هیچ‌کدام از کالاهای فایل در سبد وجود ندارند؛ فایل را دوباره از همین صفحه دانلود کنید.']);

        $this->assertSame(150000, (int) $rice->fresh()->price);
    }

    public function test_each_successful_import_replaces_the_saved_file(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin());
        $rice = $this->product('برنج هاشمی', 150000);

        $first = $this->excel([[$rice->id, 'ندارد', 'برنج هاشمی', 1600000]]);
        $second = $this->excel([[$rice->id, 'ندارد', 'برنج هاشمی', 1700000]]);

        Livewire::test(OnlineStoreRoutines::class)->set('excelFile', $first)->call('importPrices');
        Livewire::test(OnlineStoreRoutines::class)->set('excelFile', $second)->call('importPrices');

        Storage::disk('local')->assertExists(OnlineStoreRoutines::LAST_IMPORT_PATH);
        $this->assertSame([OnlineStoreRoutines::LAST_IMPORT_PATH], Storage::disk('local')->files('price-imports'));
        $this->assertSame(file_get_contents($second->getRealPath()), Storage::disk('local')->get(OnlineStoreRoutines::LAST_IMPORT_PATH));

        // A rejected file changes no price and leaves the saved file alone.
        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', $this->excel([[$rice->id, 'ندارد', 'برنج هاشمی', 'نامشخص']]))
            ->call('importPrices');

        $this->assertSame(file_get_contents($second->getRealPath()), Storage::disk('local')->get(OnlineStoreRoutines::LAST_IMPORT_PATH));
        $this->assertSame(170000, (int) $rice->fresh()->price);
    }

    public function test_import_requires_an_xlsx_file(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(OnlineStoreRoutines::class)
            ->call('importPrices')
            ->assertHasErrors(['excelFile' => 'required']);

        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', UploadedFile::fake()->createWithContent('prices.csv', "1,x,y,100\n"))
            ->call('importPrices')
            ->assertHasErrors(['excelFile' => 'mimes']);
    }

    /**
     * An .xlsx whose price cells are Excel formulas, as the commerce team fills the file (a VLOOKUP
     * into their own price list). Each formula carries the number Excel last computed for it, the way
     * Excel saves a workbook; a null number leaves the formula without one.
     *
     * @param  list<array{0: int, 1: string, 2: string, 3: int|string|null}>  $rows  id, name, formula, computed rials (or an Excel error such as #N/A)
     */
    private function excelWithFormulas(array $rows): UploadedFile
    {
        $cell = fn (string $ref, string $text) => '<c r="'.$ref.'" t="inlineStr"><is><t>'.htmlspecialchars($text).'</t></is></c>';
        $sheetRows = '<row r="1">'.implode('', array_map(fn ($heading, $col) => $cell($col.'1', $heading), OnlineStoreRoutines::HEADINGS, ['A', 'B', 'C', 'D'])).'</row>';
        foreach ($rows as $i => [$id, $name, $formula, $value]) {
            $r = $i + 2;
            $sheetRows .= '<row r="'.$r.'"><c r="A'.$r.'"><v>'.$id.'</v></c>'.$cell('B'.$r, 'ندارد').$cell('C'.$r, $name)
                .'<c r="D'.$r.'"'.(is_string($value) ? ' t="e"' : '').'><f>'.htmlspecialchars($formula).'</f>'.($value === null ? '' : '<v>'.$value.'</v>').'</c></row>';
        }

        $path = tempnam(sys_get_temp_dir(), 'formulas').'.xlsx';
        $zip = new \ZipArchive;
        $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="قیمت کالاها" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$sheetRows.'</sheetData></worksheet>');
        $zip->close();

        return UploadedFile::fake()->createWithContent('prices.xlsx', file_get_contents($path));
    }

    public function test_a_file_numbered_one_off_after_a_deleted_product_prices_each_product_by_its_name(): void
    {
        // as on the server: a product deleted in the middle, so the ids after it are one ahead of
        // a list numbered 1, 2, 3 by hand
        $this->actingAs($this->admin());
        $gone = $this->product('روغن قدیمی حذف شده', 10000);
        $nature = $this->product('روغن مایع پخت و پز 810 گرم طبیعت', 1000);
        $palm = $this->product('روغن مایع سرخ کردنی بدون پالم 810 گرم فامیلا', 1000);
        $oil = $this->product('روغن مایع سرخ کردنی 1620 گرم فامیلا', 1000);
        $pasta = $this->product('ماکارانی رشته ایی 700 گرمی زر ماکارون سایز 1.2', 1000);
        $goneId = $gone->id;
        $gone->delete();

        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', $this->excel([
                [$goneId, $goneId, 'روغن مایع پخت و پز 810 گرم طبیعت', 4020000],
                [$nature->id, $nature->id, 'روغن مایع سرخ کردنی بدون پالم 810 گرم فامیلا', 4335000],
                [$palm->id, $palm->id, 'روغن مایع سرخ کردنی 1620 گرم فامیلا', 8643000],
                [$oil->id, $oil->id, 'ماکارانی رشته ایی 700 گرمی زر ماکارون سایز 1.2', 928000],
            ]))
            ->call('importPrices')
            ->assertSet('importErrors', [])
            ->assertNotified(
                Notification::make()
                    ->title('قیمت‌ها بروزرسانی شد')
                    ->body('قیمت ۴ کالا تغییر کرد و ۰ کالا بدون تغییر ماند. ۴ سطر با نام کالا پیدا شد، چون کد یونیک آن در فایل با سبد یکی نبود (سطر ۲، ۳، ۴، ۵).')
                    ->success()
                    ->persistent()
            );

        // each price on the product the row names: the macaroni's on the macaroni, not on the oil
        $this->assertSame(402000, (int) $nature->fresh()->price);
        $this->assertSame(433500, (int) $palm->fresh()->price);
        $this->assertSame(864300, (int) $oil->fresh()->price);
        $this->assertSame(92800, (int) $pasta->fresh()->price);
    }

    public function test_an_id_with_a_name_sabad_does_not_have_changes_nothing(): void
    {
        $this->actingAs($this->admin());
        $rice = $this->product('برنج هاشمی', 150000);
        $beans = $this->product('لوبیا قرمز', 90000);

        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', $this->excel([
                [$rice->id, 'ندارد', 'برنج هاشمی', 1750000],
                [$beans->id, 'ندارد', 'نخود', 950000],
            ]))
            ->call('importPrices')
            ->assertSet('importErrors', [
                'سطر ۳: کد یونیک '.$this->fa($beans->id).' در سبد مال «لوبیا قرمز» است، ولی کالایی با نام «نخود» در سبد نیست؛ نام یا کد این سطر را اصلاح کنید.',
            ])
            ->assertNotified('هیچ قیمتی تغییر نکرد');

        $this->assertSame(150000, (int) $rice->fresh()->price);
        $this->assertSame(90000, (int) $beans->fresh()->price);
    }

    public function test_two_rows_naming_the_same_product_are_a_duplicate(): void
    {
        $this->actingAs($this->admin());
        $rice = $this->product('برنج هاشمی', 150000);
        $beans = $this->product('لوبیا قرمز', 90000);

        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', $this->excel([
                [$rice->id, 'ندارد', 'برنج هاشمی', 1750000],
                [$beans->id, 'ندارد', 'برنج هاشمی', 1800000],
            ]))
            ->call('importPrices')
            ->assertSet('importErrors', ['سطر ۳: این کالا در فایل تکراری است.']);

        $this->assertSame(150000, (int) $rice->fresh()->price);
    }

    public function test_names_typed_a_little_differently_still_match(): void
    {
        $this->actingAs($this->admin());
        $tea = $this->product('چای کیسه‌ای گلستان 100 عددی', 150000);
        $sugar = $this->product('قند شکسته 900 گرمی', 90000);
        $rice = $this->product('برنج هاشمی', 70000);

        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', $this->excel([
                // Arabic ي and ك, a space for the half-space, Persian digits, extra spaces
                [$tea->id, 'ندارد', '  چاي كيسه اي  گلستان ۱۰۰ عددی ', 1600000],
                [$sugar->id, 'ندارد', 'قند شكسته ٩٠٠ گرمي', 950000],
                // no name at all: checked by id alone, as before
                [$rice->id, 'ندارد', '', 800000],
            ]))
            ->call('importPrices')
            ->assertSet('importErrors', [])
            ->assertNotified('قیمت‌ها بروزرسانی شد');

        $this->assertSame(160000, (int) $tea->fresh()->price);
        $this->assertSame(95000, (int) $sugar->fresh()->price);
        $this->assertSame(80000, (int) $rice->fresh()->price);
    }

    public function test_a_bundles_id_with_a_products_name_prices_that_product(): void
    {
        $this->actingAs($this->admin());
        $rice = $this->product('برنج هاشمی', 150000);
        $bundle = Product::create(['name' => 'سبد اقتصادی', 'category' => Product::BUNDLE_CATEGORY, 'price' => 0, 'unit' => 'سبد']);

        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', $this->excel([[$bundle->id, 'ندارد', 'برنج هاشمی', 1750000]]))
            ->call('importPrices')
            ->assertSet('importErrors', []);

        $this->assertSame(175000, (int) $rice->fresh()->price);
    }

    public function test_the_exported_file_imports_back_without_a_single_mismatch(): void
    {
        $this->actingAs($this->admin());
        $this->product('برنج هاشمی', 150000);
        $this->product('چای کیسه‌ای گلستان', 90000);
        $gone = $this->product('کالای حذف شده', 1000);
        $this->product('روغن 1620 گرم', 80000);
        $gone->delete();

        $export = Livewire::test(OnlineStoreRoutines::class)->call('exportPrices')->effects['download'] ?? null;
        $path = tempnam(sys_get_temp_dir(), 'exp').'.xlsx';
        file_put_contents($path, base64_decode($export['content']));

        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', UploadedFile::fake()->createWithContent('prices.xlsx', file_get_contents($path)))
            ->call('importPrices')
            ->assertSet('importErrors', [])
            ->assertNotified('قیمت‌ها بروزرسانی شد');
    }

    private function fa(int $number): string
    {
        return strtr((string) $number, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
    }

    public function test_formula_prices_count_by_the_number_excel_computed(): void
    {
        $this->actingAs($this->admin());
        $rice = $this->product('برنج هاشمی', 5934500);
        $sugar = $this->product('قند صدیق', 150000);

        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', $this->excelWithFormulas([
                [$rice->id, 'برنج هاشمی', 'VLOOKUP(C2,[1]Sheet1!$A:$C,3,0)', 68575000],
                [$sugar->id, 'قند صدیق', 'VLOOKUP(C3,[1]Sheet1!$A:$C,3,0)', 1634000],
            ]))
            ->call('importPrices')
            ->assertSet('importErrors', [])
            ->assertNotified('قیمت‌ها بروزرسانی شد');

        // rials in the file, tomans on the site
        $this->assertSame(6857500, (int) $rice->fresh()->price);
        $this->assertSame(163400, (int) $sugar->fresh()->price);
    }

    public function test_a_formula_saved_without_its_number_is_reported(): void
    {
        $this->actingAs($this->admin());
        $rice = $this->product('برنج هاشمی', 5934500);

        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', $this->excelWithFormulas([
                [$rice->id, 'برنج هاشمی', 'VLOOKUP(C2,[1]Sheet1!$A:$C,3,0)', null],
            ]))
            ->call('importPrices')
            ->assertSet('importErrors', ['سطر ۲: فرمول ستون چهارم عددی ندارد؛ فایل را در اکسل باز کنید تا قیمت ها حساب شوند و دوباره ذخیره کنید.']);

        $this->assertSame(5934500, (int) $rice->fresh()->price);
    }

    public function test_a_lookup_that_found_nothing_is_reported(): void
    {
        $this->actingAs($this->admin());
        $rice = $this->product('برنج هاشمی', 5934500);

        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', $this->excelWithFormulas([
                [$rice->id, 'برنج هاشمی', 'VLOOKUP(C2,[1]Sheet1!$A:$C,3,0)', '#N/A'],
            ]))
            ->call('importPrices')
            ->assertSet('importErrors', ['سطر ۲: فرمول ستون چهارم به جای قیمت خطا داده است (مثلا #N/A)؛ احتمالا این کالا در لیست قیمتی که فرمول از آن می خواند پیدا نشد.']);

        $this->assertSame(5934500, (int) $rice->fresh()->price);
    }

    public function test_any_price_goes_through_however_far_from_the_current_one(): void
    {
        $this->actingAs($this->admin());
        $sugar = $this->product('شکر صدیق', 105500);
        $oil = $this->product('روغن', 686400);

        Livewire::test(OnlineStoreRoutines::class)
            ->set('excelFile', $this->excel([
                [$sugar->id, 'ندارد', 'شکر صدیق', 105500],
                [$oil->id, 'ندارد', 'روغن', 68640000],
            ]))
            ->call('importPrices')
            ->assertSet('importErrors', []);

        // the file is read as rials, whatever the size of the change
        $this->assertSame(10550, (int) $sugar->fresh()->price);
        $this->assertSame(6864000, (int) $oil->fresh()->price);
    }
}
