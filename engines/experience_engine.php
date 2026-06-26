<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA ADAPTIVE RESEARCH MACHINE
|--------------------------------------------------------------------------
| RC4
| Research Experience Engine
|--------------------------------------------------------------------------
|
| Tugas:
| - Menggabungkan Replay + Paper Trade
| - Membangun Experience Memory
| - Menghitung Trust
| - Menghitung Survival
| - Menghitung Stability
| - Menyiapkan data untuk Brain
|
*/

error_reporting(E_ALL);

$replayFile = dirname(__DIR__).'/storage/replay_stats.json';
$tradeFile  = dirname(__DIR__).'/storage/paper_trades.json';
$outputFile = dirname(__DIR__).'/storage/experience.json';

$replay = [];

if(file_exists($replayFile)){
    $replay = json_decode(
        file_get_contents($replayFile),
        true
    ) ?: [];
}

$trades = [];

if(file_exists($tradeFile)){
    $trades = json_decode(
        file_get_contents($tradeFile),
        true
    ) ?: [];
}

$experience = [];

/*
|--------------------------------------------------------------------------
| LOAD REPLAY EXPERIENCE
|--------------------------------------------------------------------------
*/

foreach($replay as $pattern=>$row){

    $experience[$pattern] = [
        'pattern'      => $pattern,
        'win'          => $row['win'] ?? 0,
        'loss'         => $row['loss'] ?? 0,
        'source'       => 'REPLAY',
        'trend'        => $row['trend'] ?? 'UNKNOWN',
        'market_state' => $row['market_state'] ?? 'UNKNOWN',
        'digit'        => $row['digit'] ?? '',
        'last2'        => $row['last2'] ?? '',
        'session'      => $row['session'] ?? '',
        'first_seen' => $row['first_seen'] ?? date('Y-m-d'),
        'last_seen'    => date('Y-m-d'),
        'lifecycle'    => 'DISCOVERED',
        'confidence'   => 50,
    ];

}

/*
|--------------------------------------------------------------------------
| LOAD PAPER TRADE EXPERIENCE
|--------------------------------------------------------------------------
*/

foreach($trades as $trade){

$pattern = $trade['pattern'] ?? null;

    if(!$pattern){
        continue;
    }

    if(!isset($experience[$pattern])){
        $experience[$pattern] = [
            'pattern'        => $pattern,
            'win'            => 0,
            'loss'           => 0,
            'source'         => 'LIVE',
            'trend'          => $trade['trend'] ?? 'UNKNOWN',
            'market_state'   => $trade['market_state'] ?? 'UNKNOWN',
            'digit'          => $trade['digit'] ?? '',
            'last2'          => $trade['last2'] ?? '',
            'session'        => $trade['session'] ?? '',
            'first_seen'     => date('Y-m-d'), // FIXED: Mengganti $row yang tidak valid menjadi date saat ini
            'last_seen'      => date('Y-m-d'),
            'lifecycle'      => 'DISCOVERED',
            'confidence'     => 50
        ];
    }

    if(($trade['result'] ?? '') === 'WIN'){
        $experience[$pattern]['win']++;
    }elseif(($trade['result'] ?? '') === 'LOSS'){
        $experience[$pattern]['loss']++;
    }

}

/*
|--------------------------------------------------------------------------
| BUILD RESEARCH EXPERIENCE
|--------------------------------------------------------------------------
*/

foreach($experience as $pattern=>&$row){

    $win   = $row['win'] ?? 0;
    $loss  = $row['loss'] ?? 0;
    $total = $win + $loss;

    $row['total'] = $total;

    /*
    |--------------------------------------------------------------------------
    | Trust
    |--------------------------------------------------------------------------
    */

    $row['trust'] = $total > 0 ? round(($win / $total) * 100, 2) : 50;

    /*
    |--------------------------------------------------------------------------
    | Stability
    |--------------------------------------------------------------------------
    */

    if($total >= 100){
        $stability = 'VERY_HIGH';
    }elseif($total >= 50){
        $stability = 'HIGH';
    }elseif($total >= 20){
        $stability = 'MEDIUM';
    }else{
        $stability = 'LOW';
    }

    $row['stability'] = $stability;

    /*
    |--------------------------------------------------------------------------
    | Survival
    |--------------------------------------------------------------------------
    */

    if($row['trust'] >= 85 && $total >= 300){
        $survival = 'ELITE';
    }elseif($row['trust'] >= 70 && $total >= 100){
        $survival = 'SURVIVOR';
    }elseif($row['trust'] >= 60){
        $survival = 'CANDIDATE';
    }else{
        $survival = 'UNPROVEN';
    }

    $row['survival']  = $survival;
    $row['lifecycle'] = $survival;

    /*
    |--------------------------------------------------------------------------
    | Decay
    |--------------------------------------------------------------------------
    */

    if($total < 10){
        $decay = 'UNKNOWN';
    }elseif($row['trust'] < 45){
        $decay = 'HIGH';
    }elseif($row['trust'] < 55){
        $decay = 'MEDIUM';
    }else{
        $decay = 'LOW';
    }

    $row['decay'] = $decay;

    /*
    |--------------------------------------------------------------------------
    | Research Score & Confidence
    |--------------------------------------------------------------------------
    */

    $researchScore = round(
        (($row['trust'] * 0.70) + (min($total, 100) * 0.30)),
        2
    );

    $row['research_score'] = $researchScore;

    $row['confidence'] = round(
        ($row['trust'] + $row['research_score']) / 2,
        2
    );

    /*
    |--------------------------------------------------------------------------
    | Last Update
    |--------------------------------------------------------------------------
    */
    $row['last_seen']  = date('Y-m-d');
    $row['updated_at'] = date('Y-m-d H:i:s');

}

unset($row);

/*
|--------------------------------------------------------------------------
| SORT BEST RESEARCH (FIXED LOGIC)
|--------------------------------------------------------------------------
*/

uasort($experience, function($a, $b){
    $order = [
        'ELITE'      => 5,
        'SURVIVOR'   => 4,
        'CANDIDATE'  => 3,
        'DISCOVERED' => 2,
        'UNPROVEN'   => 1
    ];

    $rankA = $order[$a['lifecycle']] ?? 0;
    $rankB = $order[$b['lifecycle']] ?? 0;

    // Jika tingkatan siklus hidup berbeda, urutkan dari rank tertinggi
    if($rankA != $rankB){
        return $rankB <=> $rankA;
    }

    // Jika tingkatannya sama, urutkan berdasarkan nilai confidence tertinggi
    return $b['confidence'] <=> $a['confidence'];
});

/*
|--------------------------------------------------------------------------
| SAVE (FIXED SYNTAX)
|--------------------------------------------------------------------------
*/

file_put_contents(
    $outputFile,
    json_encode($experience, JSON_PRETTY_PRINT)
);

echo "RC4 EXPERIENCE UPDATED";

?>