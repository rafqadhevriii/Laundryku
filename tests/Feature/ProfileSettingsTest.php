<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\PaymentFixtures;
use Tests\TestCase;

class ProfileSettingsTest extends TestCase
{
    use RefreshDatabase, PaymentFixtures;

    public function test_guests_must_log_in_before_accessing_profile(): void
    {
        $this->get('/profile')->assertRedirect(route('login'));
        $this->patch('/profile', ['name' => 'Tidak boleh'])->assertRedirect(route('login'));
    }

    public function test_owner_and_cashier_can_open_their_own_profile(): void
    {
        $this->paymentFixtures();

        foreach ([$this->owner, $this->cashier] as $user) {
            $this->actingAs($user)->get(route('profile.edit'))
                ->assertOk()
                ->assertSee($user->name)
                ->assertSee('Pengaturan Profil')
                ->assertSee('current_password', false);
            $this->get(route('dashboard'))->assertOk()
                ->assertSee('data-profile-menu', false)
                ->assertSee(route('profile.edit'), false)
                ->assertSee('Keluar akun');
        }
    }

    public function test_user_can_update_only_their_own_profile_details(): void
    {
        $this->paymentFixtures();
        $originalBusiness = $this->cashier->business_id;
        $originalBranch = $this->cashier->branch_id;

        $this->actingAs($this->cashier)->patch(route('profile.update'), [
            'name' => 'Kasir Baru',
            'username' => 'kasir_baru',
            'email' => 'kasir.baru@example.test',
            'role' => 'owner',
            'business_id' => $this->foreignBranch->business_id,
            'branch_id' => $this->foreignBranch->id,
        ])->assertRedirect(route('profile.edit'));

        $this->cashier->refresh();
        $this->assertSame('Kasir Baru', $this->cashier->name);
        $this->assertSame('kasir_baru', $this->cashier->username);
        $this->assertSame('kasir.baru@example.test', $this->cashier->email);
        $this->assertSame('cashier', $this->cashier->role);
        $this->assertSame($originalBusiness, $this->cashier->business_id);
        $this->assertSame($originalBranch, $this->cashier->branch_id);
        $this->assertSame('owner', $this->owner->fresh()->role);
    }

    public function test_profile_does_not_accept_another_users_username_or_email(): void
    {
        $this->paymentFixtures();
        $this->actingAs($this->cashier)->patch(route('profile.update'), [
            'name' => 'Kasir',
            'username' => $this->owner->username,
            'email' => $this->owner->email,
        ])->assertSessionHasErrors(['username', 'email']);
    }

    public function test_password_change_requires_current_password_and_confirmation(): void
    {
        $this->paymentFixtures();
        $this->actingAs($this->owner)->patch(route('profile.password'), [
            'current_password' => 'salah',
            'password' => 'rahasiabaru123',
            'password_confirmation' => 'rahasiabaru123',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $this->owner->fresh()->password));

        $this->patch(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'rahasiabaru123',
            'password_confirmation' => 'tidak-sama',
        ])->assertSessionHasErrors('password');

        $this->patch(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'rahasiabaru123',
            'password_confirmation' => 'rahasiabaru123',
        ])->assertRedirect(route('profile.edit'));

        $this->assertTrue(Hash::check('rahasiabaru123', $this->owner->fresh()->password));
    }
}
