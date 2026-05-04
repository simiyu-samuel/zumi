<?php

namespace Tests\Feature\Api;

use App\Models\GatedRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GatedRoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_gated_room()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/rooms', [
            'title'           => 'Live Coding Session',
            'description'     => 'Building Zumi backend.',
            'entry_fee_drops' => 200,
            'scheduled_at'    => now()->addDay()->toDateTimeString(),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('room.title', 'Live Coding Session');

        $this->assertDatabaseHas('gated_rooms', [
            'title'   => 'Live Coding Session',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_join_gated_room_with_drops()
    {
        $host = User::factory()->create();
        $participant = User::factory()->create(['drops_balance' => 1000]);
        
        $room = GatedRoom::create([
            'user_id'         => $host->id,
            'title'           => 'Paid Workshop',
            'entry_fee_drops' => 300,
            'status'          => 'scheduled',
        ]);

        Sanctum::actingAs($participant);

        $response = $this->postJson("/api/v1/rooms/{$room->id}/join");

        $response->assertStatus(200);

        $participant->refresh();
        $host->refresh();

        // 300 drops spent. 15% fee = 45. Host gets 255.
        $this->assertEquals(700, $participant->drops_balance);
        $this->assertEquals(255, $host->drops_balance);

        $this->assertDatabaseHas('gated_room_participants', [
            'user_id'       => $participant->id,
            'gated_room_id' => $room->id,
            'amount_paid'   => 300,
        ]);
    }

    public function test_host_can_start_and_end_room()
    {
        $host = User::factory()->create();
        $room = GatedRoom::create([
            'user_id'         => $host->id,
            'title'           => 'Live Room',
            'status'          => 'scheduled',
        ]);

        Sanctum::actingAs($host);

        // Start
        $this->postJson("/api/v1/rooms/{$room->id}/start")
            ->assertStatus(200)
            ->assertJsonPath('room.status', 'live');

        // End
        $this->postJson("/api/v1/rooms/{$room->id}/end")
            ->assertStatus(200)
            ->assertJsonPath('room.status', 'ended');
    }

    public function test_user_cannot_join_ended_room()
    {
        $host = User::factory()->create();
        $participant = User::factory()->create(['drops_balance' => 1000]);
        
        $room = GatedRoom::create([
            'user_id' => $host->id,
            'title'   => 'Ended Session',
            'status'  => 'ended',
        ]);

        Sanctum::actingAs($participant);

        $response = $this->postJson("/api/v1/rooms/{$room->id}/join");

        $response->assertStatus(403);
    }
}
