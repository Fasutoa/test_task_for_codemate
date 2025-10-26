<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Balance;
use PHPUnit\Framework\Attributes\Test;

class BalanceControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_deposit_money_to_existing_user(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/deposit', [
            'user_id' => $user->id,
            'amount' => 500.00,
            'comment' => 'Пополнение через карту',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Deposit successful',
                'user_id' => $user->id,
                'balance' => 500.00,
            ]);

        $this->assertDatabaseHas('balances', [
            'user_id' => $user->id,
            'balance' => 500.00,
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => 500.00,
        ]);
    }

    #[Test]
    public function it_creates_user_if_not_exists_on_deposit(): void
    {
        $response = $this->postJson('/api/deposit', [
            'user_id' => 999,
            'amount' => 100.00,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'balance',
                'user_id',
            ])
            ->assertJson([
                'message' => 'Deposit successful',
            ]);

        $createdUserId = $response->json()['user_id'];

        $this->assertDatabaseHas('users', [
            'id' => $createdUserId,
            'is_system_created' => true,
        ]);

        $this->assertDatabaseHas('balances', [
            'user_id' => $createdUserId,
            'balance' => 100.00,
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $createdUserId,
            'type' => 'deposit',
            'amount' => 100.00,
        ]);
    }

    #[Test]
    public function it_can_withdraw_money(): void
    {
        $user = User::factory()->create();
        Balance::factory()->create(['user_id' => $user->id, 'balance' => 300.00]);

        $response = $this->postJson('/api/withdraw', [
            'user_id' => $user->id,
            'amount' => 100.00,
            'comment' => 'Покупка подписки',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Withdrawal successful',
                'user_id' => $user->id,
                'balance' => 200.00,
            ]);

        $this->assertDatabaseHas('balances', [
            'user_id' => $user->id,
            'balance' => 200.00,
        ]);
    }

    #[Test]
    public function it_prevents_withdraw_if_not_enough_funds(): void
    {
        $user = User::factory()->create();
        Balance::factory()->create(['user_id' => $user->id, 'balance' => 50.00]);

        $response = $this->postJson('/api/withdraw', [
            'user_id' => $user->id,
            'amount' => 100.00,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'error' => 'Insufficient funds',
            ]);

        $this->assertDatabaseHas('balances', [
            'user_id' => $user->id,
            'balance' => 50.00,
        ]);
    }

    #[Test]
    public function it_can_transfer_money_between_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Balance::factory()->create(['user_id' => $user1->id, 'balance' => 300.00]);
        Balance::factory()->create(['user_id' => $user2->id, 'balance' => 100.00]);

        $response = $this->postJson('/api/transfer', [
            'from_user_id' => $user1->id,
            'to_user_id' => $user2->id,
            'amount' => 150.00,
            'comment' => 'Перевод другу',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Transfer successful',
            ]);

        $this->assertDatabaseHas('balances', [
            'user_id' => $user1->id,
            'balance' => 150.00,
        ]);

        $this->assertDatabaseHas('balances', [
            'user_id' => $user2->id,
            'balance' => 250.00,
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user1->id,
            'type' => 'transfer_out',
            'amount' => 150.00,
            'related_user_id' => $user2->id,
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user2->id,
            'type' => 'transfer_in',
            'amount' => 150.00,
            'related_user_id' => $user1->id,
        ]);
    }

    #[Test]
    public function it_can_get_balance(): void
    {
        $user = User::factory()->create();
        Balance::factory()->create(['user_id' => $user->id, 'balance' => 350.00]);

        $response = $this->getJson("/api/balance/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'user_id' => $user->id,
                'balance' => 350.00,
            ]);
    }
}
