<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'علی رضایی',
            'phone' => '09121234567',
            'address' => 'تهران',
            'items' => [
                ['product_name' => 'برنج', 'quantity' => 1, 'unit_price' => 1000],
            ],
        ], $overrides);
    }

    public function test_order_is_created_with_valid_data(): void
    {
        $response = $this->post(route('orders.store'), $this->validPayload());

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['phone' => '09121234567', 'full_name' => 'علی رضایی']);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_full_name_shorter_than_two_characters_is_rejected(): void
    {
        $response = $this->post(route('orders.store'), $this->validPayload(['full_name' => 'ع']));

        $response->assertSessionHasErrors('full_name');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_phone_not_matching_iranian_format_is_rejected(): void
    {
        $response = $this->post(route('orders.store'), $this->validPayload(['phone' => '0912123456']));
        $response->assertSessionHasErrors('phone');

        $response = $this->post(route('orders.store'), $this->validPayload(['phone' => '08121234567']));
        $response->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_a_customer_deleted_in_the_panel_can_order_again(): void
    {
        // deleted in the panel: the row stays (soft delete), and with it the phone, which is unique
        Customer::create(['full_name' => 'نام قدیمی', 'phone' => '09121234567', 'address' => 'قدیم'])->delete();

        $response = $this->post(route('orders.store'), $this->validPayload());

        $response->assertRedirect();
        $this->assertDatabaseCount('customers', 1);
        $customer = Customer::sole();
        $this->assertSame('علی رضایی', $customer->full_name);
        $this->assertSame('تهران', $customer->address);
        $this->assertSame($customer->id, Order::sole()->customer_id);
    }

    public function test_an_existing_customer_ordering_again_is_updated_not_duplicated(): void
    {
        Customer::create(['full_name' => 'نام قدیمی', 'phone' => '09121234567', 'address' => 'قدیم']);

        $this->post(route('orders.store'), $this->validPayload())->assertRedirect();

        $this->assertDatabaseCount('customers', 1);
        $this->assertSame('علی رضایی', Customer::sole()->full_name);
    }

    public function test_orders_one_after_another_each_get_their_own_tracking_code(): void
    {
        $this->post(route('orders.store'), $this->validPayload())->assertRedirect();
        $this->post(route('orders.store'), $this->validPayload(['phone' => '09127654321']))->assertRedirect();

        $this->assertSame(['1', '2'], Order::orderBy('id')->pluck('tracking_code')->all());
    }

    public function test_an_order_row_left_with_an_old_placeholder_code_does_not_block_new_orders(): void
    {
        // a row stuck on the old placeholder, as a failure between the two steps could leave it
        $stuck = Customer::create(['full_name' => 'قدیمی', 'phone' => '09120000000']);
        Order::create(['customer_id' => $stuck->id, 'tracking_code' => '0', 'total_price' => 1000, 'status' => 'pending']);

        $this->post(route('orders.store'), $this->validPayload())->assertRedirect();

        $this->assertDatabaseCount('orders', 2);
    }

    public function test_a_failure_part_way_saves_nothing(): void
    {
        // storing the items fails: the customer and the order made just before must not stay behind
        OrderItem::creating(fn () => throw new RuntimeException('disk full'));
        $this->withoutExceptionHandling();

        try {
            $this->post(route('orders.store'), $this->validPayload());
            $this->fail('the order should have failed');
        } catch (RuntimeException $e) {
            $this->assertSame('disk full', $e->getMessage());
        }

        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_phone_typed_with_persian_digits_is_normalized_and_accepted(): void
    {
        $response = $this->post(route('orders.store'), $this->validPayload(['phone' => '۰۹۱۲۱۲۳۴۵۶۷']));

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['phone' => '09121234567']);
    }
}
