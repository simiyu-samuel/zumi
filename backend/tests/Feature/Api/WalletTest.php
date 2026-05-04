<?php

namespace Tests\Feature\Api;

use App\Enums\DropsTransactionDirection;
use App\Enums\DropsTransactionStatus;
use App\Enums\DropsTransactionType;
use App\Models\User;
use App\Models\DropsLedger;
use App\Services\DropsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_wallet_balance_and_transactions()
    {
        $user = User::factory()->create(['drops_balance' => 1000]);
        Sanctum::actingAs($user);

        // Create some transactions
        DropsLedger::create([
            'user_id'   => $user->id,
            'type'      => DropsTransactionType::Purchase,
            'amount'    => 1000,
            'direction' => DropsTransactionDirection::Credit,
            'status'    => DropsTransactionStatus::Completed,
        ]);

        $response = $this->getJson('/api/v1/wallet');

        $response->assertStatus(200)
            ->assertJsonPath('balance', 1000)
            ->assertJsonCount(1, 'transactions.data');
    }

    public function test_drops_service_credits_balance_correctly()
    {
        $user = User::factory()->create(['drops_balance' => 0]);
        $service = app(DropsService::class);

        $service->credit($user, 500, 'earn');

        $user->refresh();
        $this->assertEquals(500, $user->drops_balance);
        $this->assertDatabaseHas('drops_ledger', [
            'user_id'   => $user->id,
            'amount'    => 500,
            'direction' => 'credit',
        ]);
    }

    public function test_drops_service_debits_balance_correctly()
    {
        $user = User::factory()->create(['drops_balance' => 1000]);
        $service = app(DropsService::class);

        $service->debit($user, 300, 'spend');

        $user->refresh();
        $this->assertEquals(700, $user->drops_balance);
        $this->assertDatabaseHas('drops_ledger', [
            'user_id'   => $user->id,
            'amount'    => 300,
            'direction' => 'debit',
        ]);
    }

    public function test_drops_service_prevents_insufficient_balance()
    {
        $user = User::factory()->create(['drops_balance' => 100]);
        $service = app(DropsService::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient Drops balance.');

        $service->debit($user, 500, 'spend');
    }

    public function test_user_can_gift_drops_to_another_user()
    {
        $sender = User::factory()->create(['drops_balance' => 1000]);
        $receiver = User::factory()->create(['drops_balance' => 0, 'role' => \App\Enums\UserRole::User]);
        
        Sanctum::actingAs($sender);

        $response = $this->postJson('/api/v1/drops/gift', [
            'receiver_id' => $receiver->id,
            'amount'      => 100,
        ]);

        $response->assertStatus(200);

        $sender->refresh();
        $receiver->refresh();

        // 100 gifted. 15% fee = 15 Drops. Receiver gets 85.
        $this->assertEquals(900, $sender->drops_balance);
        $this->assertEquals(85, $receiver->drops_balance);

        $this->assertDatabaseHas('drops_ledger', [
            'user_id'   => $sender->id,
            'amount'    => 100,
            'direction' => 'debit',
            'type'      => 'gift',
        ]);

        $this->assertDatabaseHas('drops_ledger', [
            'user_id'   => $receiver->id,
            'amount'    => 85,
            'direction' => 'credit',
            'type'      => 'gift',
        ]);

        // Fee entry
        $this->assertDatabaseHas('drops_ledger', [
            'user_id'   => null,
            'amount'    => 15,
            'direction' => 'credit',
            'type'      => 'fee',
        ]);
    }

    public function test_platform_fee_is_calculated_correctly()
    {
        $service = app(DropsService::class);
        
        $freeUser = User::factory()->create(['role' => \App\Enums\UserRole::User]);
        $proUser = User::factory()->create(['role' => \App\Enums\UserRole::Pro]);
        $studioUser = User::factory()->create(['role' => \App\Enums\UserRole::Studio]);

        $this->assertEquals(15, $service->calculatePlatformFee($freeUser, 100, DropsTransactionType::Spend));
        $this->assertEquals(10, $service->calculatePlatformFee($proUser, 100, DropsTransactionType::Spend));
        $this->assertEquals(7, $service->calculatePlatformFee($studioUser, 100, DropsTransactionType::Spend));
    }
}
