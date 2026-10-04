<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Brand;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    /** The product categories; a bundle («سبد اختصاصی») is a category of its own. */
    public const CATEGORIES = [
        'rice' => 'برنج',
        'legumes' => 'حبوبات',
        'groceries' => 'خواربار',
        Product::BUNDLE_CATEGORY => 'سبد اختصاصی',
    ];

    public static function configure(Schema $schema): Schema
    {
        // a bundle is made of other products: it has no brand, unit or VAT of its own, and its
        // price is theirs added up, never typed in
        $isBundle = fn ($get): bool => $get('category') === Product::BUNDLE_CATEGORY;

        return $schema
            ->components([
                Section::make('اطلاعات محصول')
                    ->description(fn ($get) => $isBundle($get) ? 'توضیحات اختیاری است؛ بقیه فیلدها الزامی هستند.' : 'تمام فیلدهای این پنل، الزامی هستند.')
                    ->columns(3)
                    ->columnSpanFull()
                    ->components([
                        TextInput::make('name')
                            ->label(fn ($get) => $isBundle($get) ? 'نام سبد' : 'نام محصول')
                            ->required()
                            ->dehydrateStateUsing(fn ($state) => trim($state))
                            ->rules([
                                fn ($record) => function (string $attribute, $value, \Closure $fail) use ($record) {
                                    $exists = Product::where('name', trim($value))
                                        ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                        ->exists();

                                    if ($exists) {
                                        $fail('محصولی با این نام از قبل وجود دارد.');
                                    }
                                },
                            ]),

                        TextInput::make('code')
                            ->label('کدینگ')
                            ->required()
                            ->numeric()
                            ->unique(ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'کدینگ باید منحصر به فرد باشد.',
                            ])
                            ->default(fn () => (Product::max('code') ?? 0) + 1),

                        // a product stays a product and a bundle a bundle: the choice is made when it is added
                        // («سبد اختصاصی جدید» on the list fills it in)
                        Select::make('category')
                            ->label('دسته‌بندی')
                            ->required()
                            ->options(fn (?Product $record) => match (true) {
                                $record === null => self::CATEGORIES,
                                $record->isBundle() => [Product::BUNDLE_CATEGORY => self::CATEGORIES[Product::BUNDLE_CATEGORY]],
                                default => array_diff_key(self::CATEGORIES, [Product::BUNDLE_CATEGORY => true]),
                            })
                            ->disabled(fn (?Product $record) => $record?->isBundle() ?? false)
                            ->default(fn () => request()->query('category') === Product::BUNDLE_CATEGORY ? Product::BUNDLE_CATEGORY : null)
                            ->live(),

                        Select::make('brand_id')
                            ->label('برند/سازنده')
                            ->required(fn ($get) => ! $isBundle($get))
                            ->visible(fn ($get) => ! $isBundle($get))
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->validationMessages([
                                'required' => 'انتخاب برند/سازنده الزامی است.',
                            ])
                            ->default(fn () => Brand::where('name', 'ساخت ایران')->first()?->id),

                        Select::make('unit')
                            ->label('واحد')
                            ->required(fn ($get) => ! $isBundle($get))
                            ->visible(fn ($get) => ! $isBundle($get))
                            ->options([
                                'بسته' => 'بسته',
                                'بطری' => 'بطری',
                                'جعبه' => 'جعبه',
                                'جین' => 'جین',
                                'دستگاه' => 'دستگاه',
                                'عدد' => 'عدد',
                                'کارتن' => 'کارتن',
                                'کیلوگرم' => 'کیلوگرم',
                                'گالن' => 'گالن',
                            ]),

                        // قیمت کالاهای موجود فقط از «روتین‌های فروشگاه آنلاین» (ایمپورت اکسل) تغییر می‌کند؛
                        // برای کالای جدید لازم است چون ستون قیمت در دیتابیس خالی نمی‌پذیرد.
                        TextInput::make('price')
                            ->label('قیمت')
                            ->required()
                            ->numeric()
                            ->visible(fn ($get, string $operation) => $operation === 'create' && ! $isBundle($get)),

                        Toggle::make('is_available')
                            ->label(fn ($get) => $isBundle($get) ? 'سبد موجود است؟' : 'کالا موجود است؟')
                            ->helperText(fn ($get) => $isBundle($get)
                                ? 'اگر خاموش باشد، یا یکی از کالاهای سبد ناموجود باشد، سبد در سایت «ناموجود» نمایش داده می شود و قابل افزودن به سبد خرید نیست.'
                                : 'اگر خاموش باشد، کالا در سایت «ناموجود» نمایش داده می‌شود و قابل افزودن به سبد نیست.')
                            ->default(true),

                        Toggle::make('vat_enabled')
                            ->label('قیمت با مالیات بر ارزش افزوده باشد؟')
                            ->helperText('در صورت فعال بودن، مالیات بر ارزش افزوده به قیمت پایه اضافه می‌شود.')
                            ->visible(fn ($get) => ! $isBundle($get))
                            ->live(),

                        TextInput::make('vat_percentage')
                            ->label('درصد مالیات بر ارزش افزوده')
                            ->numeric()
                            ->suffix('%')
                            ->visible(fn ($get) => ! $isBundle($get) && $get('vat_enabled')),

                        Textarea::make('description')
                            ->label('توضیحات زیر نام سبد')
                            ->placeholder('مثلا: شامل برنج، روغن، حبوبات و چای')
                            ->helperText('اگر خالی بماند، نام کالاهای سبد نوشته می شود.')
                            ->rows(2)
                            ->maxLength(300)
                            ->columnSpanFull()
                            ->visible(fn ($get) => $isBundle($get)),
                    ]),

                Section::make('کالاهای سبد')
                    ->description('قیمت سبد جمع قیمت این کالاهاست و با هر تغییر قیمت آنها (مثلا ایمپورت اکسل قیمت ها) خودکار به روز می شود.')
                    ->columnSpanFull()
                    ->visible(fn ($get) => $isBundle($get))
                    ->components([
                        Repeater::make('bundleItems')
                            ->label('کالاها')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->label('کالا')
                                    ->options(fn () => Product::singles()->orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->live()
                                    ->columnSpan(2),

                                TextInput::make('quantity')
                                    ->label('تعداد')
                                    ->required()
                                    ->integer()
                                    ->minValue(1)
                                    ->maxValue(999)
                                    ->default(1)
                                    ->live(onBlur: true),
                            ])
                            ->columns(3)
                            ->minItems(1)
                            ->required()
                            ->reorderable(false)
                            ->addActionLabel('افزودن کالا به سبد')
                            ->validationMessages([
                                'min' => 'سبد باید دست کم یک کالا داشته باشد.',
                                'required' => 'سبد باید دست کم یک کالا داشته باشد.',
                            ]),

                        TextEntry::make('bundle_price')
                            ->label('قیمت سبد')
                            ->state(function ($get): string {
                                $lines = collect($get('bundleItems') ?? [])->filter(fn ($line) => filled($line['product_id'] ?? null));
                                $prices = Product::whereIn('id', $lines->pluck('product_id'))->pluck('price', 'id');
                                $total = $lines->sum(fn ($line) => (int) ($prices[$line['product_id']] ?? 0) * max(0, (int) ($line['quantity'] ?? 0)));

                                return number_format($total).' تومان';
                            }),
                    ]),

                Section::make('عکس های محصول')
                    ->columnSpanFull()
                    ->components([
                        FileUpload::make('image')
                            ->label('تصویر محصول')
                            ->image()
                            ->disk('public')
                            ->directory('products'),
                    ]),
            ]);
    }
}
