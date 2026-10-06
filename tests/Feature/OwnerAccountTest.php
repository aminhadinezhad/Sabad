<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The owner's account (User::OWNER_EMAIL): no other admin can delete it, switch its access off or
 * take its permissions away. Everything looks as it always did.
 */
class OwnerAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
    }

    private function admin(array $attributes = [], string ...$permissions): User
    {
        $user = User::factory()->create(array_merge(['has_access' => true, 'phone' => '09120000000'], $attributes));

        foreach ($permissions as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        return $user;
    }

    public function test_another_admin_cannot_delete_the_owner_but_deletes_anyone_else(): void
    {
        $owner = $this->admin(['email' => User::OWNER_EMAIL], 'access_admins', 'access_products');
        $plain = $this->admin();
        $this->actingAs($this->admin([], 'access_admins'));

        Livewire::test(ListUsers::class)
            ->callTableAction('delete', $owner)
            ->assertNotified('این ادمین را نمی توان حذف کرد.');
        $this->assertNotNull($owner->fresh());

        Livewire::test(EditUser::class, ['record' => $owner->getRouteKey()])
            ->callAction('delete');
        $this->assertNotNull($owner->fresh());

        Livewire::test(ListUsers::class)->callTableAction('delete', $plain);
        $this->assertNull($plain->fresh());
    }

    public function test_another_admin_cannot_switch_off_the_owners_access_or_permissions_or_email(): void
    {
        $owner = $this->admin(['email' => User::OWNER_EMAIL], 'access_admins', 'access_products');
        $permissions = $owner->permissions()->pluck('id')->sort()->values()->all();
        $this->actingAs($this->admin([], 'access_admins'));

        Livewire::test(EditUser::class, ['record' => $owner->getRouteKey()])
            ->fillForm([
                'name' => 'نام تازه',
                'email' => 'someone-else@test.local',
                'has_access' => false,
                'permissions' => [],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $owner->refresh();
        $this->assertTrue($owner->has_access);
        $this->assertSame(User::OWNER_EMAIL, $owner->email);
        $this->assertSame($permissions, $owner->permissions()->pluck('id')->sort()->values()->all());
        // the rest of the account is edited as usual
        $this->assertSame('نام تازه', $owner->name);
    }

    public function test_the_owner_always_gets_in(): void
    {
        $owner = $this->admin(['email' => User::OWNER_EMAIL]);
        $owner->forceFill(['has_access' => false])->saveQuietly();

        $this->actingAs($owner->fresh())->get('/admin')->assertOk();
    }

    public function test_other_admins_are_handled_as_before(): void
    {
        $other = $this->admin([], 'access_products');
        $this->actingAs($this->admin([], 'access_admins'));

        Livewire::test(EditUser::class, ['record' => $other->getRouteKey()])
            ->fillForm(['has_access' => false, 'permissions' => []])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($other->fresh()->has_access);
        $this->assertSame(0, $other->permissions()->count());
    }
}
