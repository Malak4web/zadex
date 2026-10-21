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

    public function test_admin_can_create_ticket_via_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.tickets.store'), [
            'name' => 'Client Khalid',
            'email' => 'khalid@example.com',
            'subject' => 'New Setup Inquiry',
            'category' => 'general',
            'priority' => 'medium',
            'status' => 'open',
            'message' => 'Need help setting up cloud caching.',
        ]);

        $this->assertDatabaseHas('tickets', [
            'email' => 'khalid@example.com',
            'subject' => 'New Setup Inquiry',
        ]);

        $ticket = Ticket::where('email', 'khalid@example.com')->first();
        $response->assertRedirect(route('admin.tickets.show', $ticket));
    }

    public function test_admin_can_edit_ticket_via_form(): void
    {
        $user = User::factory()->create();

        $ticket = Ticket::create([
            'ticket_number' => 'ZX-EDIT1',
            'name' => 'Original Name',
            'email' => 'orig@example.com',
            'subject' => 'Original Subject',
            'category' => 'bug',
            'priority' => 'low',
            'status' => 'open',
            'message' => 'Original message here',
        ]);

        $response = $this->actingAs($user)->put(route('admin.tickets.update', $ticket), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'subject' => 'Updated Subject',
            'category' => 'feature',
            'priority' => 'urgent',
            'status' => 'in_progress',
            'message' => 'Updated message here',
        ]);

        $response->assertRedirect(route('admin.tickets.show', $ticket));

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'name' => 'Updated Name',
            'priority' => 'urgent',
            'status' => 'in_progress',
        ]);
    }

    public function test_admin_can_delete_ticket(): void
    {
        $user = User::factory()->create();

        $ticket = Ticket::create([
            'ticket_number' => 'ZX-DEL1',
            'name' => 'Delete Me',
            'email' => 'del@example.com',
            'subject' => 'To be deleted',
            'category' => 'general',
            'priority' => 'low',
            'status' => 'open',
            'message' => 'Please delete this',
        ]);

        $response = $this->actingAs($user)->delete(route('admin.tickets.destroy', $ticket));
        $response->assertRedirect(route('admin.tickets.index'));

        $this->assertDatabaseMissing('tickets', [
            'id' => $ticket->id,
        ]);
    }

    public function test_api_crud_endpoints(): void
    {
        // 1. Index
        $response = $this->getJson('/api/tickets');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'tickets']);

        // 2. Update via API
        $ticket = Ticket::create([
            'ticket_number' => 'ZX-APICRUD',
            'name' => 'API User',
            'email' => 'api@example.com',
            'subject' => 'API Subject',
            'category' => 'general',
            'priority' => 'medium',
            'status' => 'open',
            'message' => 'API message text',
        ]);

        $upResponse = $this->putJson("/api/tickets/{$ticket->ticket_number}", [
            'subject' => 'Modified API Subject',
            'status' => 'resolved',
        ]);
        $upResponse->assertStatus(200);
        $this->assertEquals('Modified API Subject', $ticket->fresh()->subject);
        $this->assertEquals('resolved', $ticket->fresh()->status);

        // 3. Reply via API
        $replyResponse = $this->postJson("/api/tickets/{$ticket->ticket_number}/reply", [
            'message' => 'API Reply message here',
            'status' => 'closed',
        ]);
        $replyResponse->assertStatus(200);
        $this->assertEquals('closed', $ticket->fresh()->status);

        // 4. Delete via API
        $delResponse = $this->deleteJson("/api/tickets/{$ticket->ticket_number}");
        $delResponse->assertStatus(200);
        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }
}
