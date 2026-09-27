<x-filament-panels::page>
    <div style="display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(min(100%, 22rem), 1fr)); align-items: start;">

        <x-filament::section icon="heroicon-o-arrow-down-tray">
            <x-slot name="heading">گزارش قیمت کالاها</x-slot>
            <x-slot name="description">
                در این گزارش همه کالاهای سبد با قیمت فعلی‌شان در قالب یک فایل اکسل تهیه و دانلود می‌شود.
            </x-slot>

            <x-filament::button
                color="gray"
                icon="heroicon-o-arrow-down-tray"
                wire:click="exportPrices"
                wire:target="exportPrices"
            >
                دانلود فایل اکسل قیمت‌ها
            </x-filament::button>
        </x-filament::section>

        <x-filament::section icon="heroicon-o-arrow-up-tray">
            <x-slot name="heading">بروز رسانی قیمت کالا - بالک</x-slot>
            <x-slot name="description">
                ابتدا از «گزارش قیمت کالاها» در همین صفحه خروجی بگیرید. پس از ویرایش قیمت‌ها در ستون چهارم اکسل دانلود شده،
                آن را اینجا بارگذاری کنید. در این عملیات تنها قیمت کالاها بروز رسانی می‌شود و کالا با توجه به «کد یونیک محصول»
                (ستون اول) پیدا می‌شود. فرمت و ترتیب ستون‌ها را تغییر ندهید. اگر حتی یک سطر مشکل داشته باشد، هیچ قیمتی تغییر نمی‌کند.
                سطرهای کالاهایی که از سبد حذف شده‌اند نادیده گرفته می‌شوند و در پیام پایانی اعلام می‌شوند.
                قیمت ستون چهارم به ریال است و در سایت به تومان نشان داده می شود؛ می تواند فرمول هم باشد (مثلا VLOOKUP از لیست قیمت).
            </x-slot>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <x-filament::badge color="warning" icon="heroicon-o-bell-alert" style="display: inline-flex;">
                        ستون اول = کد یونیک محصول · ستون چهارم = قیمت (ریال)
                    </x-filament::badge>
                </div>

                <form wire:submit="importPrices" style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <input
                        type="file"
                        wire:model="excelFile"
                        accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                        aria-label="فایل اکسل قیمت‌ها"
                    >

                    @error('excelFile')
                        <p style="margin: 0; font-size: 0.875rem; color: rgb(220 38 38);">{{ $message }}</p>
                    @enderror

                    <div>
                        <x-filament::button
                            type="submit"
                            icon="heroicon-o-arrow-up-tray"
                            wire:target="excelFile,importPrices"
                        >
                            ایمپورت فایل و بروز رسانی
                        </x-filament::button>
                    </div>
                </form>

                @if ($importErrors !== [])
                    <div role="alert" style="border: 1px solid rgb(220 38 38 / 0.35); background: rgb(220 38 38 / 0.08); color: rgb(220 38 38); border-radius: 0.75rem; padding: 0.75rem 1rem; font-size: 0.875rem;">
                        <p style="margin: 0 0 0.5rem; font-weight: 700;">هیچ قیمتی تغییر نکرد. این موارد را در فایل اصلاح کنید:</p>
                        <ul style="margin: 0; padding-inline-start: 1.25rem; list-style: disc;">
                            @foreach ($importErrors as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
