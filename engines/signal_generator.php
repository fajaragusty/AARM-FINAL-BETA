<?php

$storagePath = dirname(__DIR__) . '/storage/';

// 1. Load Ticks & Validasi Awal
$ticksFile = $storagePath . 'historical_ticks.json';
$ticks     = file_exists($ticksFile) ? json_decode(@file_get_contents($ticksFile), true) : [];

if (count($ticks) < 4) {
    exit;
}

// 2. Load Signals
$signalsFile = $storagePath . 'signals.json';
$signals     = file_exists($signalsFile) ? json_decode(@file_get_contents($signalsFile), true) : [];

/*
|--------------------------------------------------------------------------
| Brain Loader
|--------------------------------------------------------------------------
*/
$brainFile = $storagePath . 'brain_consensus_v4.json';
if (!file_exists($brainFile)) {
    exit;
}

$brain           = json_decode(@file_get_contents($brainFile), true) ?: [];
$dnaQuality      = $brain['dna_quality'] ?? 'D';
$fitness         = $brain['fitness'] ?? 0;
$trust           = $brain['trust'] ?? 0;
$risk            = $brain['risk'] ?? 'HIGH';
$brainConfidence = $brain['confidence'] ?? 0;
$decision        = $brain['decision'] ?? 'WAIT';
$execution       = $brain['execution'] ?? [];
	
/*
|--------------------------------------------------------------------------
| Build Current Pattern
|--------------------------------------------------------------------------
*/
$total   = count($ticks);
$context = $ticks[$total - 1];

$rsi         = $context['rsi'] ?? 50;
$trend       = $context['trend'] ?? 'SIDEWAYS';
$momentum    = $context['momentum'] ?? 'FLAT';
$marketState = $context['market_state'] ?? 'RANGING';
$ema9        = $context['ema9'] ?? 0;
$ema21       = $context['ema21'] ?? 0;

$t1 = $ticks[$total - 4]['quote'];
$t2 = $ticks[$total - 3]['quote'];
$t3 = $ticks[$total - 2]['quote'];
$t4 = $ticks[$total - 1]['quote'];

// Generate formasi pattern (e.g., 'UDU', 'UUD')
$pattern = '';
$pattern .= ($t2 > $t1) ? 'U' : 'D';
$pattern .= ($t3 > $t2) ? 'U' : 'D';
$pattern .= ($t4 > $t3) ? 'U' : 'D';

/*
|--------------------------------------------------------------------------
| Brain Consensus Decision
|--------------------------------------------------------------------------
*/
$direction = null;
if ($decision === 'FOLLOW_CALL') {
    $direction = 'CALL';
} elseif ($decision === 'FOLLOW_PUT') {
    $direction = 'PUT';
} else {
    exit;
}

/*
|--------------------------------------------------------------------------
| DNA & Risk Filter
|--------------------------------------------------------------------------
*/
if ($brainConfidence < 60) {
    exit;
}

if ($risk === 'HIGH') {
    exit;
}

/*
|--------------------------------------------------------------------------
| Signal Cooldown & Duplicate Filter
|--------------------------------------------------------------------------
*/
$last = end($signals);

if ($last) {
    // Cooldown 5 detik
    $lastTime = $last['time'] ?? 0;
    if ((time() - $lastTime) < 5) {
        exit;
    }

    // Filter Pattern & Arah yang sama berturut-turut
    if (($last['pattern'] ?? '') === $pattern && ($last['direction'] ?? '') === $direction) {
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Process Saving (Signal + Paper Trade)
|--------------------------------------------------------------------------
*/
$signalTime = time();
$paperFile  = $storagePath . 'paper_trades.json';
$paperTrades = file_exists($paperFile) ? json_decode(@file_get_contents($paperFile), true) : [];

// Ekstrak informasi digit dari tick terakhir
$lastDigit = substr((string)$t4, -1);
$last2     = substr(preg_replace('/\D/', '', (string)$t4), -2);

// Append Paper Trade
$paperTrades[] = [
    'signal_time'      => $signalTime,
    'pattern'          => $pattern,
    'brain_vote'       => $decision,
    'brain_confidence' => $brainConfidence,
    'direction'        => $direction,
    'confidence'       => $brainConfidence,
    'hour'             => date('G'),
    'digit'            => $lastDigit,
    'last2'            => $last2,
    'rsi'              => $rsi,
    'trend'            => $trend,
    'momentum'         => $momentum,
    'market_state'     => $marketState,
    'ema9'             => $ema9,
    'ema21'            => $ema21,
    'entry_price'      => $t4,
    'duration'         => $execution['duration'] ?? 3,
    'entry_datetime'   => date('Y-m-d H:i:s'),
    'engine'           => 'AARM_RC5',
    'dna_quality'      => $dnaQuality,
    'fitness'          => $fitness,
    'trust'            => $trust,
    'risk'             => $risk,
    'research_ready'   => false,
    'result'           => null
];

file_put_contents($paperFile, json_encode($paperTrades, JSON_PRETTY_PRINT));

// Append Signal
$signals[] = [
    'execution'        => $execution,
    'time'             => $signalTime,
    'pattern'          => $pattern,
    'direction'        => $direction,
    'price'            => $t4,
    'confidence'       => $brainConfidence,
    'brain_confidence' => $brainConfidence,
    'dna_quality'      => $dnaQuality,
    'fitness'          => $fitness,
    'trust'            => $trust,
    'risk'             => $risk,
    'source'           => 'AARM'
];

file_put_contents($signalsFile, json_encode($signals, JSON_PRETTY_PRINT));

echo 'SIGNAL GENERATED';