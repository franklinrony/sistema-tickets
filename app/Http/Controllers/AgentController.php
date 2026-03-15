<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Pause;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgentController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Check if agent is on an ongoing pause
        $activePause = Pause::where('user_id', $user->id)
            ->whereNull('end_time')
            ->first();

        // Get the oldest pending ticket or the one currently in progress
        $currentTicket = Ticket::where('assigned_to', $user->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('created_at', 'asc') // First in First Out or by priority index
            ->first();

        $myQueue = Ticket::where('assigned_to', $user->id)
            ->where('status', 'pending')
            ->get();

        return view('agent.dashboard', compact('activePause', 'currentTicket', 'myQueue', 'user'));
    }

    public function attendTicket(Ticket $ticket)
    {
        if ($ticket->assigned_to !== Auth::id())
            abort(403);

        $ticket->update([
            'status' => 'in_progress',
            'attended_at' => now()
        ]);

        broadcast(new \App\Events\TicketUpdated($ticket));

        return redirect()->back()->with('success', 'Atendiendo ticket ' . $ticket->prefix_id);
    }

    public function completeTicket(Ticket $ticket)
    {
        if ($ticket->assigned_to !== Auth::id())
            abort(403);

        DB::transaction(function () use ($ticket) {
            $ticket->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            $user = Auth::user();
            $user->decrement('tickets_assigned');
        });

        broadcast(new \App\Events\TicketUpdated($ticket));

        return redirect()->back()->with('success', 'Ticket ' . $ticket->prefix_id . ' completado.');
    }

    // Toggle Available / Offline
    public function toggleAvailability()
    {
        $user = Auth::user();
        if ($user->status === 'offline') {
            $user->update(['status' => 'disponible']);
            return redirect()->back()->with('success', 'Ahora estás disponible para recibir tickets.');
        }
        else {
            $user->update(['status' => 'offline']);
            return redirect()->back()->with('success', 'Has cerrado tu estación (Offline).');
        }
    }
}
