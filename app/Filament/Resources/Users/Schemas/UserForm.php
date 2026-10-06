<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(1)
                    ->components([
                        TextInput::make('name')
                            ->label('نام ادمین')
                            ->required(),

                        TextInput::make('email')
                            ->label('پست الکترونیک')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('phone')
                            ->label('شماره همراه')
                            ->tel()
                            ->required()
                            ->helperText('شماره همراه باید با 09 شروع شود و 11 رقم باشد.'),

                        Toggle::make('has_access')
                            ->label('دسترسی دارد؟')
                            ->default(true)
                            ->helperText('در صورت عدم وجود تیک، امکان ورود به سایت را نخواهد داشت.'),

                        FileUpload::make('avatar')
                            ->label('تصویر پروفایل')
                            ->avatar()
                            ->image()
                            ->disk('public')
                            ->directory('avatars'),
                    ]),

                Section::make('نقش‌ها')
                    ->description('به چه فرم‌ها/بخش‌هایی دسترسی دارد؟')
                    ->components([
                        CheckboxList::make('permissions')
                            ->label('دسترسی‌های کاربر')
                            ->relationship('permissions', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => match ($record->name) {
                                'access_customers' => 'دسترسی به مشتریان',
                                'access_orders' => 'دسترسی به سفارش‌ها',
                                'access_admins' => 'دسترسی به ادمین‌ها',
                                'access_products' => 'دسترسی به کالا و محصول',
                                'access_brands' => 'دسترسی به سازنده و برند',
                                'access_routines' => 'دسترسی به روتین‌های فروشگاه آنلاین',
                                default => $record->name,
                            })
                            ->bulkToggleable()
                            ->columns(2),
                    ]),
            ]);
    }
}
