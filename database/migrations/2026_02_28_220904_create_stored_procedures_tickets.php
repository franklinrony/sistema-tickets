<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration 
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Procedimiento 1: Asignar Ticket buscando el Agente menos cargado
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_assign_ticket;
            CREATE PROCEDURE sp_assign_ticket(IN p_ticket_id BIGINT)
            BEGIN
                DECLARE v_agent_id BIGINT;
                
                SELECT u.id INTO v_agent_id
                FROM users u
                WHERE u.status = 'disponible'
                  AND u.id NOT IN (
                      SELECT user_id FROM pauses 
                      WHERE start_time <= UTC_TIMESTAMP() AND (end_time IS NULL OR end_time >= UTC_TIMESTAMP())
                  )
                ORDER BY u.tickets_assigned ASC, u.id ASC
                LIMIT 1;
                
                IF v_agent_id IS NOT NULL THEN
                    UPDATE tickets SET assigned_to = v_agent_id WHERE id = p_ticket_id;
                    UPDATE users SET tickets_assigned = tickets_assigned + 1 WHERE id = v_agent_id;
                END IF;
            END;
        ");

        // Procedimiento 2: Recalcular o Cambiar Prioridad Manual
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_recalculate_queue;
            CREATE PROCEDURE sp_recalculate_queue(IN p_ticket_id BIGINT, IN p_new_priority VARCHAR(20))
            BEGIN
                UPDATE tickets SET priority = p_new_priority WHERE id = p_ticket_id;
            END;
        ");

        // Procedimiento 3: Transferir la Cola de un agente a los demás (balanceo equitativo dinámico)
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_transfer_agent_queue;
            CREATE PROCEDURE sp_transfer_agent_queue(IN p_from_agent_id BIGINT)
            BEGIN
                DECLARE done INT DEFAULT FALSE;
                DECLARE cur_ticket_id BIGINT;
                DECLARE cur1 CURSOR FOR SELECT id FROM tickets WHERE assigned_to = p_from_agent_id AND status = 'pending';
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
                
                OPEN cur1;
                
                read_loop: LOOP
                    FETCH cur1 INTO cur_ticket_id;
                    IF done THEN
                        LEAVE read_loop;
                    END IF;
                    
                    UPDATE tickets SET assigned_to = NULL WHERE id = cur_ticket_id;
                    UPDATE users SET tickets_assigned = GREATEST(0, tickets_assigned - 1) WHERE id = p_from_agent_id;
                    
                    CALL sp_assign_ticket(cur_ticket_id);
                END LOOP;
                
                CLOSE cur1;
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_assign_ticket");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_recalculate_queue");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_transfer_agent_queue");
    }
};
