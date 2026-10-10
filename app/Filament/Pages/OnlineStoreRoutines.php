<?php

namespace App\Filament\Pages;

use App\Helpers\PersianHelper;
use App\Models\Product;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use OpenSpout\Common\Entity\Cell\FormulaCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;
use UnitEnum;

/**
 * روتین‌های فروشگاه آنلاین: دانلود اکسل قیمت کالاها و بروزرسانی گروهی قیمت‌ها با ایمپورت همان فایل،
 * به همان روشی که در پنل سایت تامین فلات انجام می‌شود (ستون اول = کد یونیک، ستون چهارم = قیمت).
 *
 * قیمت در فایل به ریال است، همان واحد لیست قیمت بازرگانی، و در سبد به تومان ذخیره و نشان داده می شود.
 * ستون قیمت می تواند فرمول باشد (مثلا VLOOKUP از لیست قیمت بازرگانی)؛ عددی که اکسل برای آن حساب
 * کرده خوانده می شود.
 */
class OnlineStoreRoutines extends Page
{
    use WithFileUploads;

    public const HEADINGS = ['کد یونیک محصول', 'کدینگ محصول', 'نام محصول', 'قیمت (ریال)'];

    /** یک تومان = ده ریال */
    private const RIAL_PER_TOMAN = 10;

    /** آخرین فایل ایمپورت‌شده روی دیسک local (storage/app/private)؛ هر ایمپورت موفق روی قبلی نوشته می‌شود. */
    public const LAST_IMPORT_PATH = 'price-imports/last-prices.xlsx';

    private const MAX_REPORTED_ERRORS = 10;

    protected string $view = 'filament.pages.online-store-routines';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::TableCells;

    protected static string|UnitEnum|null $navigationGroup = 'مدیریت سیستم';

    protected static ?string $navigationLabel = 'روتین‌های فروشگاه آنلاین';

    protected static ?string $title = 'روتین‌های فروشگاه آنلاین';

    protected static ?string $slug = 'routines';

    public $excelFile = null;

    /** @var list<string> خطاهای آخرین ایمپورت ناموفق، برای نمایش زیر فرم */
    public array $importErrors = [];

    public static function canAccess(): bool
    {
        return Filament::auth()->user()?->can('access_routines') ?? false;
    }

    /**
     * فایل اکسل همه کالاها با قیمت فعلی؛ بعد از ویرایش ستون چهارم، همین فایل ایمپورت می‌شود.
     */
    public function exportPrices(): BinaryFileResponse
    {
        abort_unless(static::canAccess(), 403);

        $path = tempnam(sys_get_temp_dir(), 'prices').'.xlsx';

        $writer = new Writer;
        $writer->openToFile($path);
        $writer->getCurrentSheet()->setName('قیمت کالاها');
        $writer->getCurrentSheet()->setSheetView((new SheetView)->setRightToLeft(true));
        $writer->addRow(Row::fromValues(self::HEADINGS));

        // a bundle's price is its products' prices added up, so it has no row of its own here
        Product::singles()->orderBy('id')->each(function (Product $product) use ($writer) {
            $writer->addRow(Row::fromValues([
                $product->id,
                $product->code ?? 'ندارد',
                $product->name,
                (int) $product->price * self::RIAL_PER_TOMAN,
            ]));
        });

        $writer->close();

        return response()
            ->download($path, 'sabad-prices-'.now()->format('Y-m-d').'.xlsx')
            ->deleteFileAfterSend();
    }

    /**
     * کل فایل اول بررسی می‌شود؛ اگر حتی یک سطر مشکل داشته باشد هیچ قیمتی تغییر نمی‌کند.
     * سطرهای کالاهایی که دیگر در سبد نیستند، مثل پنل سایت تامین فلات، نادیده گرفته و در پیام آخر اعلام می‌شوند.
     */
    public function importPrices(): void
    {
        abort_unless(static::canAccess(), 403);

        $this->importErrors = [];

        $this->validate(
            ['excelFile' => 'required|file|mimes:xlsx|max:1024'],
            [
                'excelFile.required' => 'ابتدا فایل اکسل را انتخاب کنید.',
                'excelFile.mimes' => 'فایل باید با فرمت xlsx باشد (همان فایلی که از همین صفحه دانلود می‌شود).',
                'excelFile.max' => 'حجم فایل نباید بیشتر از ۱ مگابایت باشد.',
            ],
        );

        try {
            $rows = $this->readRows($this->excelFile->getRealPath());
        } catch (Throwable) {
            $this->rejectImport(['فایل اکسل خوانده نشد. همان فایلی را که از این صفحه دانلود کرده‌اید ویرایش و ایمپورت کنید.']);

            return;
        }

        [$prices, $skippedRows, $errors, $bundleRows] = $this->parsePrices($rows);

        if ($errors !== []) {
            $this->rejectImport($errors);

            return;
        }

        if ($prices === []) {
            $this->rejectImport([$skippedRows === []
                ? 'فایل هیچ سطر کالایی ندارد.'
                : 'هیچ‌کدام از کالاهای فایل در سبد وجود ندارند؛ فایل را دوباره از همین صفحه دانلود کنید.']);

            return;
        }

        // each product saved with a new price passes it on to the bundles it is in (Product::booted)
        $bundlePricesBefore = Product::bundles()->pluck('price', 'id');

        $changed = DB::transaction(function () use ($prices) {
            $changed = 0;

            foreach (Product::whereIn('id', array_keys($prices))->get() as $product) {
                if ((int) $product->price !== $prices[$product->id]) {
                    $product->price = $prices[$product->id];
                    $product->save();
                    $changed++;
                }
            }

            return $changed;
        });

        $bundlesChanged = Product::bundles()->pluck('price', 'id')
            ->filter(fn ($price, $id) => (int) $price !== (int) ($bundlePricesBefore[$id] ?? $price))
            ->count();

        Storage::disk('local')->putFileAs(
            dirname(self::LAST_IMPORT_PATH),
            $this->excelFile,
            basename(self::LAST_IMPORT_PATH),
        );

        $this->reset('excelFile');

        $body = 'قیمت '.PersianHelper::toPersianDigits($changed).' کالا تغییر کرد و '
            .PersianHelper::toPersianDigits(count($prices) - $changed).' کالا بدون تغییر ماند.';

        if ($bundlesChanged > 0) {
            $body .= ' قیمت '.PersianHelper::toPersianDigits($bundlesChanged).' سبد اختصاصی هم بر اساس کالاهایش به روز شد.';
        }

        if ($skippedRows !== []) {
            $body .= ' '.PersianHelper::toPersianDigits(count($skippedRows)).' سطر مربوط به کالای حذف‌شده بود و نادیده گرفته شد (سطر '
                .PersianHelper::toPersianDigits(implode('، ', $skippedRows)).').';
        }

        if ($bundleRows !== []) {
            $body .= ' '.PersianHelper::toPersianDigits(count($bundleRows)).' سطر مربوط به سبد اختصاصی بود و نادیده گرفته شد؛ قیمت سبدها از کالاهایشان حساب می شود (سطر '
                .PersianHelper::toPersianDigits(implode('، ', $bundleRows)).').';
        }

        Notification::make()
            ->title('قیمت‌ها بروزرسانی شد')
            ->body($body)
            ->success()
            ->persistent($skippedRows !== [] || $bundleRows !== [])
            ->send();
    }

    /**
     * @return array<int, array<int, mixed>> سطرهای برگه اول به‌جز سطر عنوان، با شماره سطر اکسل
     */
    private function readRows(string $path): array
    {
        $reader = new Reader;
        $reader->open($path);
        $rows = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $number => $row) {
                if ($number > 1) {
                    // a formula counts by the result Excel computed for it, which the file keeps;
                    // it comes as ['formula' => result] so the checks can say what went wrong with it
                    $rows[$number] = array_map(
                        fn ($cell) => $cell instanceof FormulaCell ? ['formula' => $cell->getComputedValue()] : $cell->getValue(),
                        $row->getCells(),
                    );
                }
            }

            break;
        }

        $reader->close();

        return $rows;
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @return array{0: array<int, int>, 1: list<int>, 2: list<string>, 3: list<int>} [قیمت‌ها به تفکیک شناسه کالا، سطرهای کالای حذف‌شده، خطاها، سطرهای سبد اختصاصی]
     */
    private function parsePrices(array $rows): array
    {
        $productIds = Product::singles()->pluck('id')->flip();
        $bundleIds = Product::bundles()->pluck('id')->flip();
        // a row's name must be its id's: a file numbered by hand (or copied from an older one) puts
        // the prices on the wrong products the moment one id is off
        $names = Product::query()->pluck('name', 'id');
        $idsByName = $names->mapWithKeys(fn (string $name, int $id) => [self::comparableName($name) => $id]);
        $prices = [];
        $skippedRows = [];
        $errors = [];
        $bundleRows = [];

        foreach ($rows as $number => $cells) {
            $rawId = $cells[0] ?? null;
            $rawPrice = $cells[3] ?? null;

            if ($this->isBlank($rawId) && $this->isBlank($rawPrice)) {
                continue;
            }

            $formula = is_array($rawPrice) ? $rawPrice['formula'] : false;
            $id = $this->toInteger($rawId);
            $rial = $this->toInteger($formula === false ? $rawPrice : $formula);
            $price = $rial === null ? null : (int) round($rial / self::RIAL_PER_TOMAN);
            $label = 'سطر '.PersianHelper::toPersianDigits($number).': ';
            $rawName = $cells[2] ?? null;
            $name = is_string($rawName) || is_numeric($rawName) ? trim((string) $rawName) : '';
            $mismatch = $id !== null && $name !== '' ? $this->nameMismatch($id, $name, $names, $idsByName) : null;

            if ($id === null) {
                $errors[] = $label.'کد یونیک محصول در ستون اول باید عدد باشد.';
            } elseif ($mismatch !== null) {
                $errors[] = $label.$mismatch;
            } elseif ($bundleIds->has($id)) {
                // a bundle's price comes from its products, whatever this row says, even an error
                $bundleRows[] = $number;
            } elseif ($formula === null || (is_string($formula) && str_starts_with($formula, '#'))) {
                // an Excel error such as #N/A (the reader gives no result for it): a VLOOKUP that
                // did not find the product
                $errors[] = $label.'فرمول ستون چهارم به جای قیمت خطا داده است (مثلا #N/A)؛ احتمالا این کالا در لیست قیمتی که فرمول از آن می خواند پیدا نشد.';
            } elseif ($formula !== false && ! $rial) {
                $errors[] = $label.'فرمول ستون چهارم عددی ندارد؛ فایل را در اکسل باز کنید تا قیمت ها حساب شوند و دوباره ذخیره کنید.';
            } elseif ($price === null || $price <= 0) {
                $errors[] = $label.'قیمت در ستون چهارم باید یک عدد صحیح بیشتر از صفر باشد.';
            } elseif (! $productIds->has($id)) {
                $skippedRows[] = $number;
            } elseif (isset($prices[$id])) {
                $errors[] = $label.'این کالا در فایل تکراری است.';
            } else {
                $prices[$id] = $price;
            }

            if (count($errors) >= self::MAX_REPORTED_ERRORS) {
                $errors[] = 'خطاهای بیشتری هم وجود دارد؛ موارد بالا را اصلاح و دوباره ایمپورت کنید.';
                break;
            }
        }

        return [$prices, $skippedRows, $errors, $bundleRows];
    }

    /**
     * عدد صحیح نامنفی از سلول؛ ارقام فارسی و جداکننده‌های هزارگان را هم می‌پذیرد.
     */
    private function toInteger(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        if (is_float($value)) {
            return $value >= 0 && floor($value) === $value ? (int) $value : null;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = str_replace([',', '٬', '،', ' '], '', PersianHelper::toEnglishDigits(trim($value)));

        return ctype_digit($value) ? (int) $value : null;
    }

    /**
     * Why a row's id and name do not go together, or null when they do (or the id is of a product
     * deleted for good, whose row is skipped as before).
     *
     * @param  Collection<int, string>  $names  every product's name by id
     * @param  Collection<string, int>  $idsByName  every id by comparable name
     */
    private function nameMismatch(int $id, string $name, $names, $idsByName): ?string
    {
        $owner = $idsByName[self::comparableName($name)] ?? null;

        if ($names->has($id)) {
            if (self::comparableName($names[$id]) === self::comparableName($name)) {
                return null;
            }

            return 'کد یونیک '.PersianHelper::toPersianDigits((string) $id).' در سبد مال «'.$names[$id].'» است، ولی نام این سطر «'.$name.'» است'
                .($owner !== null ? '؛ کد یونیک «'.$name.'» در سبد '.PersianHelper::toPersianDigits((string) $owner).' است' : '')
                .'. فایل را دوباره از همین صفحه دانلود کنید و قیمت ها را در آن وارد کنید.';
        }

        return $owner !== null
            ? 'کالای «'.$name.'» در سبد کد یونیک '.PersianHelper::toPersianDigits((string) $owner).' دارد، نه '.PersianHelper::toPersianDigits((string) $id)
                .'. فایل را دوباره از همین صفحه دانلود کنید و قیمت ها را در آن وارد کنید.'
            : null;
    }

    /** A name as typed anywhere: Arabic or Persian ی and ک, half or double spaces, either digits. */
    private static function comparableName(string $name): string
    {
        $name = str_replace(['ي', 'ى', 'ك', 'ۀ', 'ة', "\u{200C}", "\u{200D}", "\u{00A0}", 'ـ'], ['ی', 'ی', 'ک', 'ه', 'ه', ' ', '', ' ', ''], $name);
        $name = (string) PersianHelper::toEnglishDigits($name);

        return mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', $name)));
    }

    private function isBlank(mixed $value): bool
    {
        return $value === null || (is_string($value) && trim($value) === '');
    }

    /**
     * @param  list<string>  $errors
     */
    private function rejectImport(array $errors): void
    {
        $this->importErrors = $errors;

        Notification::make()
            ->title('هیچ قیمتی تغییر نکرد')
            ->body('فایل مشکل دارد؛ جزئیات زیر فرم ایمپورت آمده است.')
            ->danger()
            ->send();
    }
}
