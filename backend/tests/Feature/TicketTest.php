<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_redirects_to_tickets(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('admin.tickets.index'));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.tickets.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@zadex.com',
            'password' => bcrypt('zadex@2026'),
        ]);

        $response = $this->post(route('admin.login.post'), [
            'email' => 'admin@zadex.com',
            'password' => 'zadex@2026',
        ]);

        $response->assertRedirect(route('admin.tickets.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_dashboard_loads_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.tickets.index'));
        $response->assertStatus(200);
        $response->assertSee('Zadex');
    }

    public function test_can_submit_ticket_via_api(): void
    {
        $response = $this->postJson('/api/tickets', [
            'name' => 'Sara Smith',
            'email' => 'sara@example.com',
            'subject' => 'Issue with slider',
            'category' => 'bug',
            'priority' => 'high',
            'message' => 'The slider is not animating on mobile devices.',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'ticket' => [
                'name' => 'Sara Smith',
                'email' => 'sara@example.com',
                'subject' => 'Issue with slider',
            ],
        ]);

        $this->assertDatabaseHas('tickets', [
            'email' => 'sara@example.com',
            'subject' => 'Issue with slider',
        ]);
    }

    public function test_can_reply_to_ticket(): void
    {
        $user = User::factory()->create();

        $ticket = Ticket::create([
            'ticket_number' => 'ZX-TEST1',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'category' => 'general',
            'priority' => 'medium',
            'message' => 'Test message description',
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)->post(route('admin.tickets.reply', $ticket), [
            'message' => 'We are looking into your request.',
            'status' => 'in_progress',
        ]);

        $response->assertRedirect(route('admin.tickets.show', $ticket));

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'message' => 'We are looking into your request.',
        ]);

        $this->assertEquals('in_progress', $ticket->fresh()->status);
    }
}
