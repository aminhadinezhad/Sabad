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
 * A super admin always gets in and may do everything, and no other admin can edit, delete or
 * reset them. Only the server command makes or unmakes one.
 */
class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
    }

    private function admin(array $attributes = [], string ...$permissions): User
    {
        $user = User::factory()->create(array_merge(['has_access' => true], $attributes));

        foreach ($permissions as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        return $user;
    }

    private function superAdmin(array $attributes = []): User
    {
        $user = $this->admin($attributes);
        $user->forceFill(['is_super_admin' => true])->save();

        return $user;
    }

    public function test_a_super_admin_gets_in_and_everywhere_with_no_access_switch_or_permission(): void
    {
        $boss = $this->superAdmin(['has_access' => false]);

        $this->actingAs($boss)->get('/admin')->assertOk();
        $this->actingAs($boss)->get('/admin/products')->assertOk();
        $this->actingAs($boss)->get('/admin/users')->assertOk();
        $this->actingAs($boss)->get('/admin/routines')->assertOk();

        // an ordinary admin with the same switch off and no permissions is kept out
        $this->actingAs($this->admin(['has_access' => false]))->get('/admin')->assertForbidden();
        $this->actingAs($this->admin([]))->get('/admin/products')->assertForbidden();
    }

    public function test_other_admins_cannot_edit_reset_or_delete_a_super_admin(): void
    {
        $boss = $this->superAdmin();
        $other = $this->admin([], 'access_admins');
        $plain = $this->admin();

        $this->actingAs($other);

        $this->get('/admin/users/'.$boss->getRouteKey().'/edit')->assertForbidden();

        // the row looks like any other: edit, password reset and delete are all there
        Livewire::test(ListUsers::class)
            ->assertTableActionVisible('editLocked', $boss)
            ->assertTableActionVisible('resetPasswordLocked', $boss)
            ->assertTableActionVisible('delete', $boss)
            ->assertTableActionHidden('edit', $boss)
            ->assertTableActionHidden('resetPassword', $boss)
            // everyone else as before
            ->assertTableActionVisible('edit', $plain)
            ->assertTableActionVisible('resetPassword', $plain)
            ->assertTableActionVisible('delete', $plain)
            ->assertTableActionHidden('editLocked', $plain);

        // but each only says it cannot be done
        $password = $boss->fresh()->password;
        Livewire::test(ListUsers::class)->callTableAction('editLocked', $boss)->assertNotified('این ادمین را نمی توان ویرایش کرد.');
        Livewire::test(ListUsers::class)->callTableAction('resetPasswordLocked', $boss)->assertNotified('رمز عبور این ادمین را نمی توان تغییر داد.');
        Livewire::test(ListUsers::class)->callTableAction('delete', $boss)->assertNotified('این ادمین را نمی توان حذف کرد.');

        $this->assertNotNull($boss->fresh());
        $this->assertTrue($boss->fresh()->is_super_admin);
        $this->assertSame($password, $boss->fresh()->password);

        // an ordinary admin is deleted as before
        Livewire::test(ListUsers::class)->callTableAction('delete', $plain);
        $this->assertNull($plain->fresh());
    }

    public function test_an_admin_may_delete_their_own_account(): void
    {
        $admin = $this->admin([], 'access_admins');
        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->assertTableActionVisible('delete', $admin)
            ->callTableAction('delete', $admin);

        $this->assertNull($admin->fresh());
    }

    public function test_a_super_admin_is_never_deleted_not_even_by_themselves(): void
    {
        $boss = $this->superAdmin();
        $this->actingAs($boss);

        Livewire::test(ListUsers::class)
            ->assertTableActionVisible('delete', $boss)
            ->callTableAction('delete', $boss)
            ->assertNotified('این ادمین را نمی توان حذف کرد.');
        $this->assertNotNull($boss->fresh());

        // and not even from code
        $boss->delete();
        $this->assertNotNull($boss->fresh());
    }

    public function test_a_super_admin_edits_their_own_account_but_not_their_access(): void
    {
        $boss = $this->superAdmin(['phone' => '09120000000']);
        $this->actingAs($boss);

        Livewire::test(EditUser::class, ['record' => $boss->getRouteKey()])
            ->assertFormFieldHidden('has_access')
            ->fillForm(['name' => 'امین هادی نژاد'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('امین هادی نژاد', $boss->fresh()->name);
        $this->assertTrue($boss->fresh()->is_super_admin);
    }

    public function test_the_mark_cannot_be_set_from_the_panel_or_by_mass_assignment(): void
    {
        $sneaky = User::create(['name' => 'x', 'email' => 'x@test.local', 'password' => 'secret123', 'is_super_admin' => true]);
        $this->assertFalse((bool) $sneaky->fresh()->is_super_admin);

        $other = $this->admin([], 'access_admins');
        $this->actingAs($other);

        Livewire::test(EditUser::class, ['record' => $other->getRouteKey()])
            ->fillForm(['name' => 'y', 'is_super_admin' => true])
            ->call('save');

        $this->assertFalse((bool) $other->fresh()->is_super_admin);
    }

    public function test_the_server_command_makes_and_unmakes_a_super_admin(): void
    {
        $user = $this->admin(['email' => 'amin@test.local']);

        $this->artisan('sabad:super-admin', ['email' => 'amin@test.local'])
            ->expectsConfirmation($user->name.' (amin@test.local) مدیر کل شود؟', 'yes')
            ->assertSuccessful();
        $this->assertTrue($user->fresh()->is_super_admin);

        $this->artisan('sabad:super-admin', ['email' => 'amin@test.local', '--remove' => true])
            ->expectsConfirmation('مدیر کل بودن '.$user->name.' (amin@test.local) برداشته شود؟', 'yes')
            ->assertSuccessful();
        $this->assertFalse($user->fresh()->is_super_admin);

        $this->artisan('sabad:super-admin', ['email' => 'nobody@test.local'])->assertFailed();
    }
}
