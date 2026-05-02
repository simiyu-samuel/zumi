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
}
