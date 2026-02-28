<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Models\Ticket;
use App\Models\Pause;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $agenteRole = Role::firstOrCreate(['name' => 'agente']);

        // 2. Crear Administrador Principal
        $admin = User::firstOrCreate(
        ['email' => 'admin@sistema.com'],
        [
            'name' => 'Super Administrador',
            'password' => Hash::make('password'), // password real es "password"
            'status' => 'offline',
        ]
        );
        $admin->assignRole($adminRole);

        // 3. Crear Agentes
        $agente1 = User::firstOrCreate(
        ['email' => 'agente1@sistema.com'],
        [
            'name' => 'Agente VIP 1',
            'password' => Hash::make('password'),
            'status' => 'disponible',
            'tickets_assigned' => 0
        ]
        );
        $agente1->assignRole($agenteRole);

        $agente2 = User::firstOrCreate(
        ['email' => 'agente2@sistema.com'],
        [
            'name' => 'Agente Normal 2',
            'password' => Hash::make('password'),
            'status' => 'disponible',
            'tickets_assigned' => 0
        ]
        );
        $agente2->assignRole($agenteRole);

        $agente3 = User::firstOrCreate(
        ['email' => 'agente3@sistema.com'],
        [
            'name' => 'Agente Aprendiz 3 (Pausa Larga)',
            'password' => Hash::make('password'),
            'status' => 'pausa_larga',
            'tickets_assigned' => 0
        ]
        );
        $agente3->assignRole($agenteRole);

        // Seed some pauses for Agent 3 (Training)
        Pause::create([
            'user_id' => $agente3->id,
            'type' => 'larga',
            'reason' => 'Capacitación en nuevo proceso VIP',
            'start_time' => now()->subDay(),
            'end_time' => now()->addDays(2),
        ]);

        // Simular que Agen te 1 tiene 1 ticket activo
        $demoTicket = Ticket::create([
            'prefix_id' => 'V-000',
            'priority' => 'vip',
            'status' => 'in_progress',
            'assigned_to' => $agente1->id,
            'attended_at' => now()->subMinutes(15)
        ]);
        $agente1->update(['tickets_assigned' => 1]);

        $this->command->info('Roles, Usuarios Administradores y Agentes creados.');
        $this->command->info('Credenciales de prueba:');
        $this->command->info('Admin: admin@sistema.com / password');
        $this->command->info('Agentes: agente1@sistema.com / password');
    }
}
