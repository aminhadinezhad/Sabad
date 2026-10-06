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
 * The owner's account (User::OWNER_EMAIL): anyone else may open it, but saving, resetting its
 * password or deleting it ends in an error and changes nothing.
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

    private function owner(): User
    {
        return $this->admin(['email' => User::OWNER_EMAIL, 'name' => 'Amin Hadinezhad'], 'access_admins', 'access_products');
    }

    public function test_another_admin_edits_the_owner_freely_but_saving_changes_nothing(): void
    {
        $owner = $this->owner();
        $before = [$owner->name, $owner->email, $owner->has_access, $owner->permissions()->pluck('id')->sort()->values()->all()];
        $this->actingAs($this->admin([], 'access_admins'));

        Livewire::test(EditUser::class, ['record' => $owner->getRouteKey()])
            ->assertOk()
            ->fillForm(['name' => 'نام تازه', 'email' => 'other@test.local', 'has_access' => false, 'permissions' => []])
            ->call('save')
            ->assertNotified('امکان ذخیره تغییرات این ادمین وجود ندارد.');

        $owner->refresh();
        $this->assertSame($before, [$owner->name, $owner->email, $owner->has_access, $owner->permissions()->pluck('id')->sort()->values()->all()]);
    }

    public function test_another_admin_cannot_reset_the_owners_password(): void
    {
        $owner = $this->owner();
        $password = $owner->password;
        $this->actingAs($this->admin([], 'access_admins'));

        Livewire::test(ListUsers::class)
            ->callTableAction('resetPassword', $owner, data: ['password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
            ->assertNotified('امکان تغییر رمز عبور این ادمین وجود ندارد.');

        $this->assertSame($password, $owner->fresh()->password);
    }

    public function test_another_admin_cannot_delete_the_owner_from_the_list_or_the_edit_page(): void
    {
        $owner = $this->owner();
        $this->actingAs($this->admin([], 'access_admins'));

        Livewire::test(ListUsers::class)
            ->callTableAction('delete', $owner)
            ->assertNotified('امکان حذف این ادمین وجود ندارد.');
        $this->assertNotNull($owner->fresh());

        Livewire::test(EditUser::class, ['record' => $owner->getRouteKey()])
            ->callAction('delete')
            ->assertNotified('امکان حذف این ادمین وجود ندارد.');
        $this->assertNotNull($owner->fresh());
    }

    public function test_the_owner_changes_their_own_account(): void
    {
        $owner = $this->owner();
        $this->actingAs($owner);

        Livewire::test(EditUser::class, ['record' => $owner->getRouteKey()])
            ->fillForm(['name' => 'امین'])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame('امین', $owner->fresh()->name);

        Livewire::test(ListUsers::class)
            ->callTableAction('resetPassword', $owner, data: ['password' => 'new-password-1', 'password_confirmation' => 'new-password-1']);
        $this->assertTrue(password_verify('new-password-1', $owner->fresh()->password));
    }

    public function test_every_other_account_works_as_before(): void
    {
        $other = $this->admin([], 'access_products');
        $this->actingAs($this->admin([], 'access_admins'));

        Livewire::test(EditUser::class, ['record' => $other->getRouteKey()])
            ->fillForm(['has_access' => false])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertFalse($other->fresh()->has_access);

        Livewire::test(ListUsers::class)
            ->callTableAction('resetPassword', $other, data: ['password' => 'new-password-1', 'password_confirmation' => 'new-password-1']);
        $this->assertTrue(password_verify('new-password-1', $other->fresh()->password));

        Livewire::test(ListUsers::class)->callTableAction('delete', $other);
        $this->assertNull($other->fresh());
    }
}
