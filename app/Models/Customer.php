<?php

namespace App\Models;

use App\Helpers\PersianHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = ['full_name', 'phone', 'address'];

    /**
     * A customer deleted in the panel takes their orders with them (both soft deleted, as the
     * orders' own delete does), so no order is left without its customer.
     */
    protected static function booted(): void
    {
        static::deleted(function (Customer $customer): void {
            $customer->orders()->get()->each->delete();
        });
    }

    /** The customer and their orders go together or not at all. */
    public function delete()
    {
        return DB::transaction(fn () => parent::delete());
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /** What the delete confirmation says: how many orders go with the customer, if any. */
    public function deletionWarning(): string
    {
        $orders = $this->orders()->count();

        return $orders > 0
            ? 'این مشتری '.PersianHelper::toPersianDigits((string) $orders).' سفارش دارد. با حذف مشتری، سفارش هایش هم حذف می شوند.'
            : 'آیا برای انجام این کار مطمئن هستید؟';
    }
}
