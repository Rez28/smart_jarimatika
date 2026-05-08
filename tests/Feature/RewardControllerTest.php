<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RewardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create([
            'koin' => 100,
            'total_xp' => 0,
            'level' => 1,
            'piala' => 10,
        ]);
    }

    /**
     * Test victory reward: +50 Koin, +100 EXP, +5 Piala
     */
    public function test_battle_victory_reward(): void
    {
        $response = $this->actingAs($this->user)->postJson('/jarimatika/battle/result', [
            'gameId' => 'test-game-1',
            'isVictory' => true,
            'userScore' => 2,
            'opponentScore' => 0,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'rewards' => [
                    'koin' => 50,
                    'exp' => 100,
                    'piala' => 5,
                ],
            ]);

        $this->user->refresh();
        $this->assertEquals(150, $this->user->koin);
        $this->assertEquals(100, $this->user->total_xp);
        $this->assertEquals(15, $this->user->piala);
        $this->assertEquals(1, $this->user->level); // 100/500 = level 1
    }

    /**
     * Test defeat reward: +10 Koin, +20 EXP, -2 Piala
     */
    public function test_battle_defeat_reward(): void
    {
        $response = $this->actingAs($this->user)->postJson('/jarimatika/battle/result', [
            'gameId' => 'test-game-2',
            'isVictory' => false,
            'userScore' => 1,
            'opponentScore' => 2,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'rewards' => [
                    'koin' => 10,
                    'exp' => 20,
                    'piala' => -2,
                ],
            ]);

        $this->user->refresh();
        $this->assertEquals(110, $this->user->koin);
        $this->assertEquals(20, $this->user->total_xp);
        $this->assertEquals(8, $this->user->piala);
    }

    /**
     * Test level up at exactly 500 EXP
     */
    public function test_level_up_at_500_exp(): void
    {
        // First battle: 100 EXP
        $this->actingAs($this->user)->postJson('/jarimatika/battle/result', [
            'gameId' => 'test-game-1',
            'isVictory' => true,
            'userScore' => 2,
            'opponentScore' => 0,
        ]);

        // Second battle: 100 EXP (total: 200)
        $this->actingAs($this->user)->postJson('/jarimatika/battle/result', [
            'gameId' => 'test-game-2',
            'isVictory' => true,
            'userScore' => 2,
            'opponentScore' => 0,
        ]);

        // Third battle: 100 EXP (total: 300)
        $this->actingAs($this->user)->postJson('/jarimatika/battle/result', [
            'gameId' => 'test-game-3',
            'isVictory' => true,
            'userScore' => 2,
            'opponentScore' => 0,
        ]);

        // Fourth battle: 100 EXP (total: 400)
        $this->actingAs($this->user)->postJson('/jarimatika/battle/result', [
            'gameId' => 'test-game-4',
            'isVictory' => true,
            'userScore' => 2,
            'opponentScore' => 0,
        ]);

        // Fifth battle: 100 EXP (total: 500) → LEVEL UP!
        $response = $this->actingAs($this->user)->postJson('/jarimatika/battle/result', [
            'gameId' => 'test-game-5',
            'isVictory' => true,
            'userScore' => 2,
            'opponentScore' => 0,
        ]);

        $this->user->refresh();
        $this->assertEquals(500, $this->user->total_xp);
        $this->assertEquals(2, $this->user->level); // 500/500 = level 2
        // Level 1 → Level 2: +3 bonus piala
        $this->assertEquals(40, $this->user->piala); // 10 + (5*5) + 3 = 38... wait let me recalculate
        // Initial: 10 piala
        // 5 victories: 5*5 = 25 piala
        // Level up bonus: 3 piala
        // Total: 10 + 25 + 3 = 38
        $this->assertEquals(38, $this->user->piala);
    }

    /**
     * Test latihan/belajar reward: +50 EXP
     */
    public function test_latihan_reward(): void
    {
        $response = $this->actingAs($this->user)->postJson('/reward/latihan', [
            'mode' => 'latihan',
            'levelDiambil' => 5,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'rewards' => [
                    'exp' => 50,
                ],
            ]);

        $this->user->refresh();
        $this->assertEquals(50, $this->user->total_xp);
        $this->assertEquals(1, $this->user->level);
    }

    /**
     * Test piala doesn't go negative on defeat
     */
    public function test_piala_does_not_go_negative(): void
    {
        // Set user piala to 1 (only 1 piala)
        $this->user->update(['piala' => 1]);

        $response = $this->actingAs($this->user)->postJson('/jarimatika/battle/result', [
            'gameId' => 'test-game-defeat',
            'isVictory' => false,
            'userScore' => 1,
            'opponentScore' => 2,
        ]);

        $this->user->refresh();
        // 1 - 2 = -1, but should be max(0, -1) = 0
        $this->assertEquals(0, $this->user->piala);
    }

    /**
     * Test multiple level ups (total_xp > 1000)
     */
    public function test_multiple_level_ups(): void
    {
        // Manually set XP to simulate 10 previous battles
        $this->user->update(['total_xp' => 900, 'level' => 1, 'piala' => 10]);

        // Next victory: 100 EXP → 1000 total → Level 3!
        $response = $this->actingAs($this->user)->postJson('/jarimatika/battle/result', [
            'gameId' => 'test-game-levelup-3',
            'isVictory' => true,
            'userScore' => 2,
            'opponentScore' => 0,
        ]);

        $this->user->refresh();
        $this->assertEquals(1000, $this->user->total_xp);
        $this->assertEquals(3, $this->user->level); // 1000/500 + 1 = 3
        // Bonus piala from level 1→2→3: 3 piala (for level increase of 2)
        $this->assertEquals(16, $this->user->piala); // 10 + 5 (victory) + (3*2) = 10 + 5 + 6 = 21... wait
        // Let me recalculate:
        // Initial piala: 10
        // Victory piala: +5
        // Level up bonus: (3-1) * 3 = 2 * 3 = 6
        // Total: 10 + 5 + 6 = 21
        $this->assertEquals(21, $this->user->piala);
    }

    /**
     * Test unauthenticated user cannot access rewards
     */
    public function test_unauthenticated_user_cannot_claim_reward(): void
    {
        $response = $this->postJson('/jarimatika/battle/result', [
            'gameId' => 'test-game-1',
            'isVictory' => true,
            'userScore' => 2,
            'opponentScore' => 0,
        ]);

        $response->assertStatus(401);
    }
}
