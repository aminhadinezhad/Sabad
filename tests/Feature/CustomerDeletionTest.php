<?php

namespace Tests\Feature;

use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Deleting a customer in the panel deletes their orders too (both soft), says so first, and leaves
 * everyone else's orders alone.
 */
class CustomerDeletionTest extends TestCase
{
    use RefreshDatabase;

    private Customer $ali;

    private Customer $sara;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
        $user = User::factory()->create(['has_access' => true]);
        foreach (['access_customers', 'access_orders'] as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        $this->actingAs($user);

        $this->ali = Customer::create(['full_name' => 'علی', 'phone' => '09120000001']);
        $this->sara = Customer::create(['full_name' => 'سارا', 'phone' => '09120000002']);
        $this->order($this->ali, 'A1');
        $this->order($this->ali, 'A2');
        $this->order($this->sara, 'S1');
    }

    private function order(Customer $customer, string $code): Order
    {
        return Order::create(['customer_id' => $customer->id, 'tracking_code' => $code, 'total_price' => 1000, 'status' => 'pending']);
    }

    public function test_deleting_from_the_list_takes_the_customers_orders_and_only_theirs(): void
    {
        Livewire::test(ListCustomers::class)->callTableAction('delete', $this->ali);

        $this->assertSoftDeleted($this->ali);
        $this->assertSame(['A1', 'A2'], Order::onlyTrashed()->orderBy('tracking_code')->pluck('tracking_code')->all());
        $this->assertSame(['S1'], Order::pluck('tracking_code')->all());
        $this->assertNotSoftDeleted($this->sara);
        // soft: nothing is gone from the database
        $this->assertSame(3, Order::withTrashed()->count());
    }

    public function test_deleting_from_the_customers_page_does_the_same(): void
    {
        Livewire::test(EditCustomer::class, ['record' => $this->ali->getRouteKey()])->callAction('delete');

        $this->assertSoftDeleted($this->ali);
        $this->assertSame(['S1'], Order::pluck('tracking_code')->all());
    }

    public function test_the_confirmation_says_how_many_orders_go_with_the_customer(): void
    {
        Livewire::test(ListCustomers::class)
            ->mountTableAction('delete', $this->ali)
            ->assertMountedActionModalSee('این مشتری ۲ سفارش دارد. با حذف مشتری، سفارش هایش هم حذف می شوند.');

        Livewire::test(EditCustomer::class, ['record' => $this->ali->getRouteKey()])
            ->mountAction('delete')
            ->assertMountedActionModalSee('این مشتری ۲ سفارش دارد. با حذف مشتری، سفارش هایش هم حذف می شوند.');

        // an order deleted before does not count; with none left, the old question
        Order::where('tracking_code', 'S1')->first()->delete();
        $this->assertSame('آیا برای انجام این کار مطمئن هستید؟', $this->sara->fresh()->deletionWarning());
        Livewire::test(ListCustomers::class)
            ->mountTableAction('delete', $this->sara)
            ->assertMountedActionModalSee('آیا برای انجام این کار مطمئن هستید؟')
            ->assertMountedActionModalDontSee('سفارش دارد');
    }

    public function test_the_orders_list_no_longer_shows_them(): void
    {
        Livewire::test(ListCustomers::class)->callTableAction('delete', $this->ali);

        Livewire::test(ListOrders::class)
            ->assertCanSeeTableRecords(Order::where('tracking_code', 'S1')->get())
            ->assertCanNotSeeTableRecords(Order::onlyTrashed()->get());
    }

    public function test_if_an_order_can_not_be_deleted_the_customer_stays_too(): void
    {
        Order::deleting(fn (Order $order) => $order->tracking_code === 'A2' ? throw new RuntimeException('locked') : null);

        try {
            $this->ali->delete();
            $this->fail('the delete should have failed');
        } catch (RuntimeException $e) {
            $this->assertSame('locked', $e->getMessage());
        }

        $this->assertNotSoftDeleted($this->ali);
        $this->assertSame(['A1', 'A2', 'S1'], Order::orderBy('tracking_code')->pluck('tracking_code')->all());
    }

    public function test_a_deleted_customer_who_orders_again_comes_back_without_the_old_orders(): void
    {
        $this->ali->delete();

        $this->post(route('orders.store'), [
            'full_name' => 'علی',
            'phone' => '09120000001',
            'items' => [['product_name' => 'برنج', 'quantity' => 1, 'unit_price' => 1000]],
        ])->assertRedirect();

        $this->assertNotSoftDeleted($this->ali->fresh());
        $this->assertSame(1, $this->ali->fresh()->orders()->count());
        $this->assertSame(2, $this->ali->fresh()->orders()->onlyTrashed()->count());
    }
}
