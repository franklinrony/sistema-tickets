<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    /**
     * Store a newly created ticket via public kiosk/client screen.
     */
    public function store(Request $request)
    {
        $request->validate([
            'prefixType' => 'required|in:V,N'
        ]);

        $prefix = $request->prefixType;

        // Generate a sequential ID per prefix
        $lastTicket = Ticket::where('prefix_id', 'LIKE', "{$prefix}-%")->orderBy('id', 'desc')->first();
        $sequence = 1;
        if ($lastTicket) {
            $parts = explode('-', $lastTicket->prefix_id);
            $sequence = (int)end($parts) + 1;
        }

        $formattedPrefixId = sprintf('%s-%03d', $prefix, $sequence);
        $priority = ($prefix === 'V') ? 'vip' : 'normal';

        DB::beginTransaction();
        try {
            $ticket = Ticket::create([
                'prefix_id' => $formattedPrefixId,
                'priority' => $priority,
                'status' => 'pending'
            ]);

            // Assign dynamically using our Stored Procedure logic
            DB::unprepared("CALL sp_assign_ticket({$ticket->id})");

            $ticket->refresh();
            DB::commit();

            return redirect()->back()->with('success', "Ticket generado exitosamente: {$ticket->prefix_id}. Prioridad: {$ticket->priority}");
        }
        catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ocurrió un error al generar el ticket.');
        }
    }

    /**
     * Admin modifies the priority manually (Inmediato, VIP, Normal)
     */
    public function updatePriority(Request $request, Ticket $ticket)
    {
        $request->validate([
            'priority' => 'required|in:inmediato,vip,normal'
        ]);

        $newPriority = $request->priority;

        DB::beginTransaction();
        try {
            // Call SP to recalculate queue (updates priority)
            DB::unprepared("CALL sp_recalculate_queue({$ticket->id}, '{$newPriority}')");

            // Wait, if it becomes Inmediato we might want to prioritize it manually
            // For now, the Stored Procedure handles priority assignment mapping internally when queried dynamically
            $ticket->refresh();

            // If it was unattended but now is INMEDIATO and unassigned or want to force it to a specific queue
            if ($newPriority === 'inmediato' && $ticket->status === 'pending') {
                // Here we could unassign it and re-run sp_assign_ticket to grab the most available
                DB::table('tickets')->where('id', $ticket->id)->update(['assigned_to' => null]);
                if ($ticket->assigned_to) {
                    DB::table('users')->where('id', $ticket->assigned_to)
                        ->decrement('tickets_assigned');
                }
                DB::unprepared("CALL sp_assign_ticket({$ticket->id})");
            }
            DB::commit();

            return redirect()->back()->with('success', 'Prioridad actualizada y cola recalculada');
        }
        catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error actualizando prioridad');
        }
    }
}
