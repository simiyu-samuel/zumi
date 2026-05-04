<?php

namespace App\Console\Commands;

use App\Enums\GatedRoomStatus;
use App\Models\GatedRoom;
use App\Services\GatedRoomService;
use Illuminate\Console\Command;

class ManageGatedRooms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zumi:manage-rooms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle gated room reminders and auto-transitions.';

    /**
     * Execute the console command.
     */
    public function handle(GatedRoomService $roomService): void
    {
        // 1. Reminders (10 minutes before)
        $upcoming = GatedRoom::where('status', GatedRoomStatus::Scheduled)
            ->where('scheduled_at', '<=', now()->addMinutes(10))
            ->where('scheduled_at', '>', now())
            ->get();

        foreach ($upcoming as $room) {
            // Notify host and participants (logic to be implemented in NotificationService)
            // $room->host->notify(new RoomStartingSoonNotification($room));
            $this->info("Reminder sent for room: {$room->id}");
        }

        // 2. Auto-End (Live for > 3 hours)
        $stale = GatedRoom::where('status', GatedRoomStatus::Live)
            ->where('started_at', '<=', now()->subHours(3))
            ->get();

        foreach ($stale as $room) {
            $roomService->endRoom($room);
            $this->warn("Auto-ended stale room: {$room->id}");
        }
    }
}
