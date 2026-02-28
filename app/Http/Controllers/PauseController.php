<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pause;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PauseController extends Controller
{
    /**
     * Store (start) a pause for the authenticated agent
     */
    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'type' => 'required|in:corta,larga',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time'
        ]);

        $user = Auth::user();

        $pause = Pause::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'reason' => $request->reason,
            'start_time' => $request->start_time ?? now(),
            'end_time' => $request->end_time // Can be null meaning it's ongoing
        ]);

        if ($request->type === 'corta' && !$request->end_time) {
            $user->update(['status' => 'pausa_corta']);
        }
        elseif ($request->type === 'larga' && !$request->end_time) {
            $user->update(['status' => 'pausa_larga']);
        }

        return redirect()->back()->with('success', 'Pausa iniciada: ' . $request->reason);
    }

    /**
     * Stop an ongoing pause
     */
    public function update(Pause $pause)
    {
        if ($pause->user_id !== Auth::id() && !Auth::user()->can('manage_agents')) {
            abort(403);
        }

        $pause->update(['end_time' => now()]);

        $user = User::find($pause->user_id);
        $user->update(['status' => 'disponible']);

        return redirect()->back()->with('success', 'Pausa finalizada. Estás disponible de nuevo.');
    }
}
