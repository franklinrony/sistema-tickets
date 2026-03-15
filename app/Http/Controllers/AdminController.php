<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Pause;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        // Rango de fechas para el reporte (por defecto hoy)
        $dateFrom = $request->input('date_from', Carbon::today()->toDateString());
        $dateTo = $request->input('date_to', Carbon::today()->toDateString());

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();

        // 1. KPI: Número de veces que hubo pausa corta vs larga agrupado por agente
        $pausesKpi = DB::table('pauses')->select(
            'user_id',
            DB::raw('SUM(CASE WHEN type = "corta" THEN 1 ELSE 0 END) as total_cortas'),
            DB::raw('SUM(CASE WHEN type = "larga" THEN 1 ELSE 0 END) as total_largas')
        )
            ->whereBetween('start_time', [$from, $to])
            ->groupBy('user_id')
            ->get();

        // 2. KPI: Cantidad de personas atendidas por agente (tickets completed)
        $attentionKpi = DB::table('tickets')->select(
            'assigned_to',
            DB::raw('COUNT(*) as total_attended')
        )
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$from, $to])
            ->groupBy('assigned_to')
            ->get();

        // 3. KPI: Tiempo promedio de atención por usuario (completed_at - attended_at)
        $timeKpi = DB::table('tickets')
            ->select(
            'assigned_to',
            DB::raw('AVG(TIMESTAMPDIFF(MINUTE, attended_at, completed_at)) as avg_minutes')
        )
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$from, $to])
            ->groupBy('assigned_to')
            ->get();

        // 4. KPI: Tipos de ticket atendidos por periodo (V vs N)
        $ticketTypes = DB::table('tickets')
            ->select('priority', DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('priority')
            ->pluck('total', 'priority')->toArray();

        // Combinando KPIs por agente
        $agentsData = User::role('agente')->get()->map(function ($agent) use ($pausesKpi, $attentionKpi, $timeKpi) {
            $pause = $pausesKpi->firstWhere('user_id', $agent->id);
            $attention = $attentionKpi->firstWhere('assigned_to', $agent->id);
            $time = $timeKpi->firstWhere('assigned_to', $agent->id);

            return [
            'name' => $agent->name,
            'status' => $agent->status,
            'cortas' => $pause->total_cortas ?? 0,
            'largas' => $pause->total_largas ?? 0,
            'attended' => $attention->total_attended ?? 0,
            'avg_time' => round($time->avg_minutes ?? 0, 1),
            'current_queue' => $agent->tickets_assigned,
            'id' => $agent->id
            ];
        });

        // Cola global en progreso
        $activeQueue = Ticket::with('agent')->whereIn('status', ['pending', 'in_progress'])->orderBy('created_at', 'asc')->get();

        // Pausas largas programadas o activas (vacaciones, incapacidades)
        $scheduledPauses = Pause::with('agent')
            ->where('type', 'larga')
            ->where(function ($q) {
            $q->whereNull('end_time')->orWhere('end_time', '>=', now());
        })
            ->orderBy('start_time', 'asc')
            ->get();

        $allAgents = User::role('agente')->get();

        return view('admin.dashboard', compact('agentsData', 'allAgents', 'ticketTypes', 'activeQueue', 'scheduledPauses', 'dateFrom', 'dateTo'));
    }

    public function transferQueue(Request $request)
    {
        $request->validate([
            'from_agent_id' => 'required|exists:users,id'
        ]);

        DB::beginTransaction();
        try {
            DB::unprepared("CALL sp_transfer_agent_queue({$request->from_agent_id})");
            DB::commit();
            return redirect()->back()->with('success', 'La cola del agente fue redistribuida equitativamente entre los agentes disponibles usando sp_transfer_agent_queue.');
        }
        catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ocurrió un error en la transferencia de cola.');
        }
    }

    public function storePause(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        Pause::create([
            'user_id' => $request->user_id,
            'type' => 'larga',
            'reason' => $request->reason,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return redirect()->back()->with('success', 'Ausencia o pausa larga programada exitosamente.');
    }

    public function destroyPause(Pause $pause)
    {
        $pause->delete();
        return redirect()->back()->with('success', 'Ausencia o pausa larga eliminada.');
    }
}
