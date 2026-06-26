<?php

error_reporting(E_ALL);
date_default_timezone_set('Asia/Jakarta');

/*
|--------------------------------------------------------------------------
| AARM FINAL - PAPER TRADE ENGINE
|--------------------------------------------------------------------------
*/

$tickFile  = dirname(__DIR__) . '/storage/historical_ticks.json';
$tradeFile = dirname(__DIR__) . '/storage/paper_trades.json';

if (!file_exists($tickFile)) {
    exit('TICK FILE NOT FOUND');
}

if (!file_exists($tradeFile)) {
    exit('PAPER TRADE FILE NOT FOUND');
}

/*
|--------------------------------------------------------------------------
| Load Data
|--------------------------------------------------------------------------
*/
$ticks  = json_decode(file_get_contents($tickFile), true) ?: [];
$trades = json_decode(file_get_contents($tradeFile), true) ?: [];

if (count($ticks) < 5) {
    exit('NOT ENOUGH TICKS');
}

/*
|--------------------------------------------------------------------------
| Statistics & Engine Setup
|--------------------------------------------------------------------------
*/
$updated      = 0;
$totalWin     = 0;
$totalLoss    = 0;
$currentTick  = end($ticks);
$currentPrice = $currentTick['quote'] ?? 0;

/*
|--------------------------------------------------------------------------
| Evaluate Trades
|--------------------------------------------------------------------------
*/
foreach ($trades as &$trade) {
    // Jika trade sudah terevaluasi sebelumnya, langsung hitung statistiknya
    if (isset($trade['result']) && $trade['result'] !== null) {
        if ($trade['result'] === 'WIN') {
            $totalWin++;
        } elseif ($trade['result'] === 'LOSS') {
            $totalLoss++;
        }
        continue;
    }

    $entryTime  = $trade['signal_time'] ?? 0;
    $entryPrice = $trade['entry_price'] ?? 0;
    $direction  = strtoupper($trade['direction'] ?? '');
    $duration   = (int)($trade['duration'] ?? 3);

    $ticksPassed = 0;
    $exitPrice   = null;

    /*
    |--------------------------------------------------------------------------
    | Find Exit Tick (Optimized Sequential Search)
    |--------------------------------------------------------------------------
    */
    foreach ($ticks as $tick) {
        if (($tick['epoch'] ?? 0) <= $entryTime) {
            continue;
        }

        $ticksPassed++;

        if ($ticksPassed >= $duration) {
            $exitPrice = $tick['quote'] ?? 0;
            break;
        }
    }

    // Jika jumlah tick setelah entry belum memenuhi durasi kontrak, skip (masih running)
    if ($ticksPassed < $duration) {
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | Result Determination
    |--------------------------------------------------------------------------
    */
    $result = 'LOSS';

    if ($direction === 'CALL' && $exitPrice > $entryPrice) {
        $result = 'WIN';
    } elseif ($direction === 'PUT' && $exitPrice < $entryPrice) {
        $result = 'WIN';
    }

    // Snapshot Context & Metadata Update
    $trade['exit_price']     = $exitPrice;
    $trade['trend']          = $trade['trend'] ?? 'UNKNOWN';
    $trade['market_state']   = $trade['market_state'] ?? 'UNKNOWN';
    $trade['ema9']           = $trade['ema9'] ?? null;
    $trade['ema21']          = $trade['ema21'] ?? null;
    $trade['rsi']            = $trade['rsi'] ?? null;
    $trade['confidence']     = $trade['confidence'] ?? 0;
    $trade['risk']           = $trade['risk'] ?? 'UNKNOWN';
    $trade['brain_vote']     = $trade['brain_vote'] ?? 'UNKNOWN';
    $trade['dna_quality']    = $trade['dna_quality'] ?? 'UNKNOWN';
    $trade['result']         = $result;
    $trade['closed_at']      = time();
    $trade['holding_ticks']  = $duration;
    $trade['profit_points']  = round(abs($exitPrice - $entryPrice), 5);
    $trade['evaluated_by']   = 'AARM';
    $trade['engine_version'] = 'FINAL';
    $trade['evaluated_at']   = date('Y-m-d H:i:s');
    $trade['research_ready'] = true;

    $updated++;

    if ($result === 'WIN') {
        $totalWin++;
    } else {
        $totalLoss++;
    }
}
unset($trade); // Memutus referensi & pointer loop

/*
|--------------------------------------------------------------------------
| Save & Summary Calculation
|--------------------------------------------------------------------------
*/
file_put_contents($tradeFile, json_encode($trades, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$totalTrades = $totalWin + $totalLoss;
$winRate     = $totalTrades > 0 ? round(($totalWin / $totalTrades) * 100, 2) : 0;

$summaryFile = dirname(__DIR__) . '/storage/paper_trade_summary.json';
$summary = [
    'updated'      => $updated,
    'total_trades' => $totalTrades,
    'win'          => $totalWin,
    'loss'         => $totalLoss,
    'wr'           => $winRate,
    'last_update'  => date('Y-m-d H:i:s'),
    'engine'       => 'AARM FINAL'
];

file_put_contents($summaryFile, json_encode($summary, JSON_PRETTY_PRINT));

/*
|--------------------------------------------------------------------------
| Console Output
|--------------------------------------------------------------------------
*/
echo PHP_EOL;
echo "==========================================" . PHP_EOL;
echo "          AARM PAPER TRADE ENGINE         " . PHP_EOL;
echo "==========================================" . PHP_EOL;
echo "Updated       : " . $updated . PHP_EOL;
echo "Total Trade   : " . $totalTrades . PHP_EOL;
echo "WIN           : " . $totalWin . PHP_EOL;
echo "LOSS          : " . $totalLoss . PHP_EOL;
echo "WR            : " . $winRate . " %" . PHP_EOL;
echo "Current Price : " . $currentPrice . PHP_EOL;
echo "Saved Paths   : paper_trades.json & summary.json" . PHP_EOL;
echo "==========================================" . PHP_EOL;