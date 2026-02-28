<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PauseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $activeTickets = \App\Models\Ticket::where('status', 'in_progress')->with('agent')->get();
    $recentTickets = \App\Models\Ticket::where('status', 'completed')
        ->whereDate('completed_at', \Carbon\Carbon::today())
        ->orderBy('completed_at', 'desc')
        ->take(10)
        ->get();

    return view('welcome', compact('activeTickets', 'recentTickets'));
});

// Ruta publica para clientes y creación de tickets (Kiosco)
Route::post('/tickets', [TicketController::class , 'store'])->name('tickets.store');

Route::middleware(['auth', 'verified'])->group(function () {

    // Redirección dinámica basada en roles
    Route::get('/dashboard', function () {
            if (auth()->user()->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('agent.dashboard');
        }
        )->name('dashboard');

        Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class , 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class , 'destroy'])->name('profile.destroy');

        // Rutas protegidas genéricas
        Route::patch('/tickets/{ticket}/priority', [TicketController::class , 'updatePriority'])->name('tickets.update_priority');

        // Rutas del Agente
        Route::prefix('agent')->group(function () {
            Route::get('/dashboard', [AgentController::class , 'index'])->name('agent.dashboard');
            Route::post('/toggle-availability', [AgentController::class , 'toggleAvailability'])->name('agent.toggle');
            Route::patch('/tickets/{ticket}/attend', [AgentController::class , 'attendTicket'])->name('agent.attend_ticket');
            Route::patch('/tickets/{ticket}/complete', [AgentController::class , 'completeTicket'])->name('agent.complete_ticket');

            // Pausas
            Route::post('/pauses', [PauseController::class , 'store'])->name('pauses.store');
            Route::patch('/pauses/{pause}', [PauseController::class , 'update'])->name('pauses.update');
        }
        );

        // Rutas del Administrador
        Route::prefix('admin')->middleware(['role:admin'])->group(function () {
            Route::get('/dashboard', [AdminController::class , 'dashboard'])->name('admin.dashboard');
            Route::post('/transfer-queue', [AdminController::class , 'transferQueue'])->name('admin.transfer_queue');
        }
        );
    });

require __DIR__ . '/auth.php';
