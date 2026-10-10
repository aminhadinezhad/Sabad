<?php

namespace App\Http\Controllers;

use App\Helpers\PersianHelper;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\PersianDate;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // ۱. اعتبارسنجی اطلاعات ورودی
        $validated = $this->validateOrderRequest($request);

        // ۲. پیدا کردن یا ساختن مشتری بر اساس شماره تلفن
        // A customer deleted in the panel keeps its row, and the phone is unique: look among the
        // deleted too and bring that one back, or the insert of a second row fails.
        $customer = Customer::withTrashed()->firstOrNew(['phone' => $validated['phone']]);
        $customer->fill([
            'full_name' => $validated['full_name'],
            'address' => $validated['address'] ?? null,
        ]);
        if ($customer->trashed()) {
            $customer->deleted_at = null;
        }
        $customer->save();

        // ۳. محاسبه‌ی جمع کل قیمت (شامل مالیات و ارزش‌افزوده)
        $totalPrice = collect($validated['items'])->sum(function ($item) {
            return $item['quantity'] * $item['unit_price'] + ($item['vat_amount'] ?? 0);
        });

        // ۴. ساخت سفارش
        $order = Order::create([
            'customer_id' => $customer->id,
            'tracking_code' => '0',
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        // به‌روز‌رسانی tracking_code با order ID
        $order->update(['tracking_code' => (string) $order->id]);

        // ۵. ذخیره‌ی آیتم‌های سفارش
        foreach ($validated['items'] as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_name' => $item['product_name'],
                'product_code' => $item['product_code'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'vat_amount' => $item['vat_amount'] ?? 0,
            ]);
        }

        // ۶. ریدایرکت به صفحه‌ی موفقیت
        return redirect()->to($order->successUrl());
    }

    public function preview(Request $request)
    {
        $validated = $this->validateOrderRequest($request);

        $totalPrice = collect($validated['items'])->sum(function ($item) {
            return $item['quantity'] * $item['unit_price'] + ($item['vat_amount'] ?? 0);
        });

        // تبدیل vat_amount به کلید «tax» چون ویوی پیش‌فاکتور با همین اسم می‌خونتش
        $items = collect($validated['items'])->map(function ($item) {
            $item['tax'] = $item['vat_amount'] ?? 0;

            return $item;
        })->all();

        return view('invoice.show', [
            'customer' => $validated,
            'items' => $items,
            'totalPrice' => $totalPrice,
            'trackingCode' => null,
            'orderDate' => PersianDate::dateTime(now()),
            'proformaUrl' => url()->current(),
            'amountInWords' => PersianHelper::numberToPersianWords($totalPrice),
        ]);
    }

    public function success($trackingCode)
    {
        $order = Order::where('tracking_code', $trackingCode)->firstOrFail();
        $items = $order->items()->get();

        return view('orders.success', ['order' => $order, 'items' => $items]);
    }

    public function invoice(Order $order)
    {
        $items = $order->items->map(function ($item) {
            return [
                'product_code' => $item->product_code,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'tax' => $item->vat_amount ?? 0,
            ];
        })->toArray();

        return view('invoice.show', [
            'orderDate' => PersianDate::dateTime($order->created_at),
            'trackingCode' => $order->tracking_code,
            'customer' => [
                'full_name' => $order->customer->full_name,
                'phone' => $order->customer->phone,
                'address' => $order->customer->address,
            ],
            'items' => $items,
            'totalPrice' => $order->total_price,
            'amountInWords' => PersianHelper::numberToPersianWords($order->total_price),
            'proformaUrl' => $order->invoiceUrl(),
        ]);
    }

    /**
     * اعتبارسنجی مشترک فرم ثبت سفارش (نام، شماره تلفن، آدرس و آیتم‌های سبد).
     * قبل از اعتبارسنجی، ارقام فارسی/عربی شماره تلفن (در صورت تایپ با کیبورد فارسی)
     * به ارقام انگلیسی نرمالایز می‌شوند.
     */
    private function validateOrderRequest(Request $request): array
    {
        $request->merge([
            'phone' => PersianHelper::toEnglishDigits($request->input('phone')),
        ]);

        return $request->validate([
            'full_name' => 'required|string|min:2|max:255',
            'phone' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
            'address' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.product_code' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.vat_amount' => 'nullable|numeric|min:0',
        ], [
            'full_name.min' => 'نام و نام خانوادگی باید حداقل ۲ حرف باشد.',
            'phone.regex' => 'شماره همراه باید با 09 شروع شود و ۱۱ رقم باشد.',
        ]);
    }
}
