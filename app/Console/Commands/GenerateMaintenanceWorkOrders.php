<?php

namespace App\Console\Commands;

use App\Models\MaintenanceSchedule;
use App\Models\Notification;
use App\Models\WorkOrder;
use App\Models\WorkOrderActivityLog;
use App\Models\WorkOrderChecklistItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateMaintenanceWorkOrders extends Command
{
    protected $signature = 'maintenance:generate-work-orders';
    protected $description = 'Generate work orders for any maintenance schedule that is due, skipping schedules with an occurrence already open';

    private const DEFAULT_CHECKLIST = [
        'Inspect asset and confirm reported issue',
        'Diagnose root cause',
        'Perform repair or replacement',
        'Test asset functionality',
        'Document work and close out',
    ];

    public function handle(): int
    {
        $dueSchedules = MaintenanceSchedule::with('asset', 'defaultAssignee')
            ->where('is_active', true)
            ->where('next_due_date', '<=', now()->toDateString())
            ->where(function ($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->whereDoesntHave('workOrders', function ($query) {
                $query->whereIn('status', ['pending', 'assigned', 'in_progress']);
            })
            ->get();

        if ($dueSchedules->isEmpty()) {
            $this->info('No maintenance schedules are due.');
            return self::SUCCESS;
        }

        foreach ($dueSchedules as $schedule) {
            DB::transaction(function () use ($schedule) {
                $nextNumber = (WorkOrder::max('id') ?? 0) + 1;
                $workOrderNumber = 'WO-' . now()->year . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

                $workOrder = WorkOrder::create([
                    'work_order_number' => $workOrderNumber,
                    'asset_id'          => $schedule->asset_id,
                    'title'             => $schedule->title,
                    'description'       => $schedule->description ?? 'Recurring preventive maintenance, auto-generated from schedule.',
                    'priority'          => 'medium',
                    'maintenance_type'  => 'preventive',
                    'status'            => 'assigned',
                    'requested_by'      => $schedule->created_by,
                    'assigned_to'       => $schedule->default_assignee_id,
                    'approved_by'       => $schedule->created_by,
                    'reported_at'       => now(),
                    'approved_at'       => now(),
                    'due_date'          => now()->addDays(7),
                    'schedule_id'       => $schedule->id,
                ]);

                foreach (self::DEFAULT_CHECKLIST as $index => $description) {
                    WorkOrderChecklistItem::create([
                        'work_order_id' => $workOrder->id,
                        'description'   => $description,
                        'sort_order'    => $index,
                    ]);
                }

                WorkOrderActivityLog::create([
                    'work_order_id'     => $workOrder->id,
                    'event_description' => "Generated from recurring maintenance schedule: {$schedule->title}",
                    'created_by'        => $schedule->created_by,
                ]);

                if ($schedule->default_assignee_id) {
                    Notification::create([
                        'recipient_id' => $schedule->default_assignee_id,
                        'type'         => 'work_order.scheduled',
                        'channel'      => 'in_app',
                        'title'        => 'Scheduled maintenance due',
                        'body'         => "{$schedule->title} on {$schedule->asset->asset_tag} is due — a work order has been created for you.",
                        'related_type' => 'work_order',
                        'related_id'   => $workOrder->id,
                    ]);
                }

                $schedule->update(['last_generated_at' => now()]);

                $this->info("Generated {$workOrderNumber} for schedule: {$schedule->title}");
            });
        }

        return self::SUCCESS;
    }
}
