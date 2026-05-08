# Game Loop WebRTC 1vs1 - Integration Test Guide

## Overview
Dokumentasi ini menjelaskan flow terbaru dari game loop WebRTC 1vs1 dengan Score Race mode dan reward system.

## Game Flow (Frontend)

### 1. Initial Setup
```
1. Player 1 clicks "Nyalakan Kamera" → establishes WebRTC connection
2. Player 2 joins & connects WebRTC
3. Both players see each other's video
```

### 2. Ready System (One-Click)
```
1. Player clicks "👋 Ready" ONLY ONCE
2. Button changes to "⏳ Menunggu Lawan..."
3. Wait for opponent to also click Ready
4. When both are ready → 3-2-1 countdown displays
5. After countdown → Game starts automatically
```

### 3. Score Race Mode
```
Game Config: WINNING_SCORE = 2 (for testing, change to 10 for production)

Battle Flow:
1. Game displays target number (adaptive difficulty)
2. Player forms hand number to match target
3. Hold position for 1.5 seconds (HOLD_DURATION)
4. On correct detection:
   - Score increases: 1/2 → 2/2
   - Victory message displays: "🎉 KAMU MENANG!"
   - saveBattleResult() called with victory=true
   - Backend processes rewards
   - After 3 seconds, reset to Ready screen

5. On incorrect/opponent scores:
   - Opponent's score increases in real-time
   - When opponent reaches 2: endBattle(false) called
   - Defeat message displays: "😢 KAMU KALAH!"
   - saveBattleResult() called with victory=false
   - After 3 seconds, reset to Ready screen
```

### 4. Auto Question Generation
```
When player answers correctly:
1. Button shows "BERHASIL! ✅" for 800ms
2. After 800ms → Next question auto-generates
3. NO manual button clicks needed
4. Continue until someone reaches WINNING_SCORE
```

## Backend Flow (Laravel)

### Endpoint: POST /jarimatika/battle/result

**Request Payload:**
```json
{
  "gameId": "peer-xxxxx",
  "isVictory": true,
  "userScore": 2,
  "opponentScore": 0
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Selamat! Kamu menang dan mendapat reward!",
  "rewards": {
    "koin": 50,
    "exp": 100,
    "piala": 5
  },
  "user": {
    "koin": 150,
    "total_xp": 500,
    "level": 2,
    "piala": 25
  }
}
```

### Reward Logic

#### Victory: +50 Koin, +100 EXP, +5 Piala
```php
$user->koin += 50;
$user->total_xp += 100;
$user->piala += 5;
checkLevelUp($user); // Check: 100/500 = no level up yet
```

#### Defeat: +10 Koin, +20 EXP, -2 Piala
```php
$user->koin += 10;
$user->total_xp += 20;
$user->piala = max(0, $user->piala - 2); // Prevent negative piala
checkLevelUp($user);
```

### Level Up System

**Formula:** `Level = intdiv(total_xp, 500) + 1`

**Examples:**
- 0-499 XP → Level 1
- 500-999 XP → Level 2
- 1000-1499 XP → Level 3
- etc.

**Level Up Bonus:** +3 Piala per level

**Example Scenario:**
```
User has 450 XP (Level 1)
Wins battle: +100 EXP → 550 XP
New level: intdiv(550, 500) + 1 = 2
Bonus: +3 Piala
```

### Endpoint: POST /reward/latihan

**Request Payload:**
```json
{
  "mode": "latihan",
  "levelDiambil": 5
}
```

**Response:**
```json
{
  "success": true,
  "message": "Hebat! Kamu telah menyelesaikan mode latihan dan mendapat +50 EXP!",
  "rewards": {
    "exp": 50
  },
  "user": {
    "total_xp": 600,
    "level": 2
  }
}
```

## Testing Checklist

### Frontend Tests
- [ ] Player can click "Nyalakan Kamera" once
- [ ] Player can click "👋 Ready" once
- [ ] 3-2-1 countdown shows correctly
- [ ] After countdown, game starts automatically
- [ ] Target number displays
- [ ] Correct hand detection triggers score increase
- [ ] Question auto-generates without manual clicks
- [ ] Score updates: "1/2" → "2/2"
- [ ] Victory message shows when reaching 2 points
- [ ] Defeat message shows when opponent wins
- [ ] Both players see same score updates in real-time

### Backend Tests
```bash
# Test victory
curl -X POST http://localhost:8000/jarimatika/battle/result \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: {csrf_token}" \
  -H "Cookie: XSRF-TOKEN={csrf_token}" \
  -d '{
    "gameId": "test-game-1",
    "isVictory": true,
    "userScore": 2,
    "opponentScore": 0
  }'

# Test defeat
curl -X POST http://localhost:8000/jarimatika/battle/result \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: {csrf_token}" \
  -H "Cookie: XSRF-TOKEN={csrf_token}" \
  -d '{
    "gameId": "test-game-2",
    "isVictory": false,
    "userScore": 1,
    "opponentScore": 2
  }'

# Test latihan reward
curl -X POST http://localhost:8000/reward/latihan \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: {csrf_token}" \
  -H "Cookie: XSRF-TOKEN={csrf_token}" \
  -d '{
    "mode": "latihan",
    "levelDiambil": 3
  }'
```

### Database Verification
```sql
-- Check user stats
SELECT id, name, koin, total_xp, level, piala FROM users WHERE id = 1;

-- Check level calculation
SELECT 
  id, 
  total_xp,
  FLOOR(total_xp / 500) + 1 as calculated_level,
  level as stored_level
FROM users;

-- Check EXP progress
SELECT 
  id,
  total_xp,
  total_xp % 500 as exp_progress,
  CEIL((FLOOR(total_xp / 500) + 1) * 500 - total_xp) as exp_to_next_level
FROM users;
```

## Configuration (Frontend)

**To change winning score:**
```javascript
// In jarimatika-battle-new.js
const WINNING_SCORE = 2;  // Change from 2 to 10 for production
```

**To change hold duration:**
```javascript
// In jarimatika-battle-new.js
const HOLD_DURATION = 1500; // milliseconds (currently 1.5 seconds)
```

## Common Issues & Solutions

### Issue: Battle doesn't end after reaching score
**Solution:** Ensure `saveBattleResult()` is called and routes are registered

### Issue: Level doesn't increase
**Solution:** 
- Check `intdiv($user->total_xp, 500) + 1` calculation
- Verify `checkLevelUp()` is called after XP update
- Ensure model is saved with `$user->save()`

### Issue: Opponent score not updating
**Solution:**
- Verify Pusher is configured correctly
- Check `channel.bind("OpponentScored", ...)` listener
- Ensure `sendScoreToOpponent()` is called on line 662

### Issue: Piala becomes negative
**Solution:** Add check in controller: `max(0, $user->piala - 2)`

## Future Enhancements
- [ ] Achievement system based on streaks
- [ ] Daily bonus rewards
- [ ] Weekly leaderboard
- [ ] Battle replay/stats
- [ ] Difficulty multiplier for higher scores
- [ ] Item bonus based on equipped items
