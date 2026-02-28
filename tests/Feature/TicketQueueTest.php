<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Pause;
use Illuminate\Support\Facades\DB;

class TicketQueueTest extends TestCase
{
    use DatabaseTransactions;

    public function test_assign_ticket_to_available_agent_with_least_load(): void
    {
        // Agent 1: Available, 2 tickets
        $agent1 = User::factory()->create(['status' => 'disponible', 'tickets_assigned' => 2]);

        // Agent 2: Available, 1 ticket (Should receive the ticket)
        $agent2 = User::factory()->create(['status' => 'disponible', 'tickets_assigned' => 1]);

        // Agent 3: Offline, 0 tickets (Should be ignored)
        $agent3 = User::factory()->create(['status' => 'offline', 'tickets_assigned' => 0]);

        // Agent 4: Available but currently on short pause (Should be ignored)
        $agent4 = User::factory()->create(['status' => 'disponible', 'tickets_assigned' => 0]);
        Pause::create([
            'user_id' => $agent4->id,
            'type' => 'corta',
            'reason' => 'baño',
            'start_time' => now()->subMinutes(5),
            'end_time' => now()->addMinutes(5),
        ]);

        $ticket = Ticket::create([
            'prefix_id' => 'V-001',
            'priority' => 'vip',
            'status' => 'pending'
        ]);

        // Call the stored procedure
        DB::unprepared("CALL sp_assign_ticket({$ticket->id})");

        // Refresh entities state
        $ticket->refresh();
        $agent1->refresh();
        $agent2->refresh();

        $this->assertEquals($agent2->id, $ticket->assigned_to, "El ticket no se asignó al agente disponible con menor carga");
        $this->assertEquals(2, $agent2->tickets_assigned, "El contador de tickets del agente no se incrementó");
        $this->assertEquals(2, $agent1->tickets_assigned, "El agente 1 no debió recibir el ticket");
    }

    public function test_transfer_agent_queue_distributes_tickets(): void
    {
        // Agent 1: Available, 0 tickets (will receive the transfer)
        $agent1 = User::factory()->create(['status' => 'disponible', 'tickets_assigned' => 0]);

        // Agent 2: Leaving with 1 pending ticket
        $agent2 = User::factory()->create(['status' => 'offline', 'tickets_assigned' => 1]);

        $ticket = Ticket::create([
            'prefix_id' => 'N-001',
            'priority' => 'normal',
            'status' => 'pending',
            'assigned_to' => $agent2->id
        ]);

        // Proceed to transfer queue from Agent 2
        DB::unprepared("CALL sp_transfer_agent_queue({$agent2->id})");

        $ticket->refresh();
        $agent1->refresh();
        $agent2->refresh();

        // Ticket should be re-assigned from Agent 2 to Agent 1
        $this->assertEquals($agent1->id, $ticket->assigned_to);
        $this->assertEquals(1, $agent1->tickets_assigned);
        $this->assertEquals(0, $agent2->tickets_assigned);
    }
}
