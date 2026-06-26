<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA ADAPTIVE RESEARCH MACHINE
|--------------------------------------------------------------------------
| RC5
| Research Collector
|--------------------------------------------------------------------------
|
| Tugas:
| - Mengambil historical tick
| - Membentuk Research Snapshot
| - Menyimpan metadata penelitian
| - Tidak mengambil keputusan BUY/SELL
|
*/

error_reporting(E_ALL);

$historyFile = dirname(__DIR__) . '/storage/historical_ticks.json';
$outputFile  = dirname(__DIR__) . '/storage/research_ticks.json';

if (!file_exists($historyFile)) {
    exit('historical_ticks.json NOT FOUND');
}

$ticks = json_decode(file_get_contents($historyFile), true);

if (!is_array($ticks)) {
    $ticks = [];
}

$total = count($ticks);

if ($total == 0) {

    echo json_encode([
        'status' => 'EMPTY',
        'total'  => 0
    ]);

    exit;
}

$last = end($ticks);

$quote = $last['quote'] ?? 0;

$quoteString = (string)$quote;

$digit = substr($quoteString, -1);

$numeric = preg_replace('/\D/', '', $quoteString);

$last2 = strlen($numeric) >= 2
    ? substr($numeric, -2)
    : $numeric;

$research = [

    'research_time' => date('Y-m-d H:i:s'),

    'tick_index'    => $total,

    'quote'         => $quote,

    'digit'         => $digit,

    'last2'         => $last2,

    'trend'         => $last['trend'] ?? 'UNKNOWN',

    'market_state'  => $last['market_state'] ?? 'UNKNOWN',

    'momentum'      => $last['momentum'] ?? 'UNKNOWN',

    'ema9'          => $last['ema9'] ?? null,

    'ema21'         => $last['ema21'] ?? null,

    'rsi'           => $last['rsi'] ?? null,

    'volatility'    => $last['volatility'] ?? null,

    'session_hour'  => date('G'),

    'weekday'       => date('N'),

    'collector'     => 'RC4_RESEARCH',

    'status'        => 'OBSERVED'

];

file_put_contents(

    $outputFile,

    json_encode(
        $research,
        JSON_PRETTY_PRINT
    )

);

echo json_encode([

    'status' => 'OK',

    'collector' => 'RC4',

    'tick_count' => $total,

    'research' => $research

], JSON_PRETTY_PRINT);

?>