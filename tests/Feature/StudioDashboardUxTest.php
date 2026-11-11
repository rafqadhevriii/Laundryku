<?php
namespace Tests\Feature;

use App\Services\ActiveBranchContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PaymentFixtures;
use Tests\TestCase;

class StudioDashboardUxTest extends TestCase
{
    use RefreshDatabase, PaymentFixtures;

    public function test_owner_sees_a_simplified_dashboard_and_a_contextual_location_picker(): void
    {
        $this->paymentFixtures();
        $this->actingAs($this->owner)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('studio-dashboard', false)
            ->assertSee('studio-stats', false)
            ->assertSee('Buat pesanan')
            ->assertSee('data-open-branch', false)
            ->assertSee('studio-location-details', false)
            ->assertSee(route('profile.edit'), false)
            ->assertDontSee('sidebar-workspace', false);
    }

    public function test_owner_with_selected_branch_can_open_create_order_directly(): void
    {
        $this->paymentFixtures();
        $key = app(ActiveBranchContext::class)->sessionKey($this->owner);
        $this->actingAs($this->owner)->withSession([$key => $this->branch->id])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('orders.create'), false)
            ->assertDontSee('data-open-branch', false);
    }

    public function test_cashier_has_no_business_branch_picker_and_keeps_create_order_access(): void
    {
        $this->paymentFixtures();
        $this->actingAs($this->cashier)->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('orders.create'), false)
            ->assertDontSee('studio-location-details', false)
            ->assertDontSee('data-open-branch', false);
    }
}
