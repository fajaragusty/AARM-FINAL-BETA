<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA ADAPTIVE RESEARCH MACHINE
|--------------------------------------------------------------------------
| TREND RESEARCH ENGINE RC5
|--------------------------------------------------------------------------
| Purpose :
| Research kualitas Trend
| BUKAN menghasilkan sinyal
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);
date_default_timezone_set('Asia/Jakarta');

$root       = dirname(__DIR__);
$storage    = $root . '/storage';
$paperFile  = $storage . '/paper_trades.json';
$outputFile = $storage . '/trend_research.json';

if (!file_exists($paperFile)) {
    echo "[ERROR] Paper trades file not found." . PHP_EOL;
    exit;
}

$paperTrades = json_decode(file_get_contents($paperFile), true) ?: [];
$research    = [];

foreach ($paperTrades as $trade) {
    if (empty($trade['result'])) {
        continue;
    }

    $trend       = strtoupper($trade['trend'] ?? 'UNKNOWN');
    $marketState = strtoupper($trade['market_state'] ?? 'UNKNOWN');
    $rsiZone     = 'UNKNOWN';
    $rsi         = $trade['rsi'] ?? null;

    if (is_numeric($rsi)) {
        if ($rsi >= 70) {
            $rsiZone = 'OVERBOUGHT';
        } elseif ($rsi <= 30) {
            $rsiZone = 'OVERSOLD';
        } elseif ($rsi >= 50) {
            $rsiZone = 'BULLISH';
        } else {
            $rsiZone = 'BEARISH';
        }
    }

    $emaAlignment = false;
    if (
        isset($trade['ema9'], $trade['ema21'])
        && is_numeric($trade['ema9'])
        && is_numeric($trade['ema21'])
    ) {
        $emaAlignment = ($trade['ema9'] > $trade['ema21']);
    }

    /*
    |--------------------------------------------------------------------------
    | Perbaikan Bug: Definisikan $key SEBELUM digunakan sebagai index array
    |--------------------------------------------------------------------------
    */
    $key = implode('|', [
        $trend,
        $marketState,
        $rsiZone,
        $emaAlignment ? 'EMA1' : 'EMA0'
    ]);

    // Inisialisasi struktur kombinasi pattern baru jika belum ada
    if (!isset($research[$key])) {
        $research[$key] = [
            'pattern'        => $key, // Bertindak sebagai pattern unik multivariat
            'trend'          => $trend,
            'market_state'   => $marketState,
            'rsi_zone'       => $rsiZone,
            'ema_alignment'  => $emaAlignment,
            'brain_support'  => 0,
            'feedback_score' => 0,
            'samples'        => 0,
            'win'            => 0,
            'loss'           => 0,
            'wr'             => 0,
            'trust'          => 50,
            'confidence'     => 50,
            'survival'       => 'DISCOVERED',
            'knowledge'      => 'LEARNING',
            'research_age'   => 1,
            'elite'          => false,
            'updated_at'     => date('Y-m-d H:i:s')
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Perbaikan Bug Akumulasi: Gunakan $key, BUKAN $trend tunggal
    |--------------------------------------------------------------------------
    */
    $research[$key]['samples']++;
    
    if ($emaAlignment) {
        $research[$key]['brain_support']++;
    }

    if (strtoupper($trade['result']) === 'WIN') {
        $research[$key]['feedback_score']++;
        $research[$key]['win']++;
    } else {
        $research[$key]['loss']++;
    }
}

// Finalisasi kalkulasi statistik untuk setiap kombinasi pattern
foreach ($research as &$row) {
    if ($row['samples'] > 0) {
        $row['wr'] = round(($row['win'] / $row['samples']) * 100, 2);
    }

    // Kalkulasi Trust Score (Bobot WR 70% + Bobot Volume Sampel 30%)
    $row['trust'] = round(
        ($row['wr'] * 0.70) + (min($row['samples'], 500) / 500 * 30),
        2
    );

    // Kalkulasi Confidence Score
    $row['confidence'] = round(($row['trust'] + $row['wr']) / 2, 2);

    // Penentuan Kasta Evolusi Mutasi
    if ($row['trust'] >= 90 && $row['samples'] >= 300) {
        $row['survival']  = 'ELITE';
        $row['knowledge'] = 'MASTER';
        $row['elite']     = true;
    } elseif ($row['trust'] >= 75 && $row['samples'] >= 100) {
        $row['survival']  = 'SURVIVOR';
        $row['knowledge'] = 'STABLE';
    } elseif ($row['trust'] >= 60) {
        $row['survival']  = 'CANDIDATE';
        $row['knowledge'] = 'PROMISING';
    } else {
        $row['survival']  = 'DISCOVERED';
        $row['knowledge'] = 'LEARNING';
    }
}
unset($row);

// Urutkan dari nilai Trust tertinggi ke terendah
usort($research, function ($a, $b) {
    return $b['trust'] <=> $a['trust'];
});

// Simpan hasil riset trend ke file output
file_put_contents(
    $outputFile,
    json_encode(array_values($research), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

echo "TREND RESEARCH COMPLETE" . PHP_EOL;