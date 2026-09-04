<?php

namespace Tests\Feature;

use App\Models\ClientOrg;
use App\Models\Corridor;
use App\Models\DispatcherUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionControllerTest extends TestCase
{
    use RefreshDatabase;

    private ClientOrg $org;
    private DispatcherUser $user;

    protected function setUp(): void
    {
        parent::setUp();

        $corridor = Corridor::create([
            'name' => 'Lagos–Accra',
            'origin' => 'Lagos',
            'destination' => 'Accra',
        ]);

        $this->org = ClientOrg::create([
            'name' => 'Test Coop',
            'type' => 'cooperative',
            'corridor_id' => $corridor->id,
        ]);

        $this->user = DispatcherUser::create([
            'org_id' => $this->org->id,
            'name' => 'James Dispatcher',
            'phone_number' => '+2348010000001',
            'role' => 'dispatcher',
            'password' => 'password',
        ]);
    }

    public function test_login_page_loads(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    public function test_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'phone_number' => '+2348010000001',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_login_with_invalid_password_fails(): void
    {
        $response = $this->post('/login', [
            'phone_number' => '+2348010000001',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('phone_number');
        $this->assertGuest();
    }

    public function test_login_with_unknown_phone_number_fails(): void
    {
        $response = $this->post('/login', [
            'phone_number' => '+2340000000000',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('phone_number');
        $this->assertGuest();
    }

    public function test_logout(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_dashboard_requires_auth(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_loads_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
    }
}