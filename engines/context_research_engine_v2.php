<?php

$history = json_decode(
    file_get_contents(
        dirname(__DIR__).'/storage/historical_ticks.json'
    ),
    true
) ?: [];

if(count($history) < 100){
    exit('NOT ENOUGH HISTORICAL DATA');
}

$knowledge = [];

/*
|--------------------------------------------------------------------------
| RSI Zone Helper
|--------------------------------------------------------------------------
*/

function getRsiZone($rsi){
    if($rsi < 20)  return 'RSI_00_20';
    if($rsi < 40)  return 'RSI_20_40';
    if($rsi < 60)  return 'RSI_40_60';
    if($rsi < 80)  return 'RSI_60_80';
    return 'RSI_80_100';
}

/*
|--------------------------------------------------------------------------
| Research Loop
|--------------------------------------------------------------------------
*/

for($i=3; $i<count($history)-5; $i++){

    $t1 = $history[$i-3]['quote'];
    $t2 = $history[$i-2]['quote'];
    $t3 = $history[$i-1]['quote'];
    $t4 = $history[$i]['quote'];

    $pattern = '';
    $pattern .= ($t2 > $t1) ? 'U' : 'D';
    $pattern .= ($t3 > $t2) ? 'U' : 'D';
    $pattern .= ($t4 > $t3) ? 'U' : 'D';

/*
|--------------------------------------------------------------------------
| Safe Context
|--------------------------------------------------------------------------
*/

$rsi = (float)($history[$i]['rsi'] ?? 50);

$rsiZone = getRsiZone($rsi);

$trend = $history[$i]['trend'] ?? 'SIDEWAYS';

$marketState = $history[$i]['market_state'] ?? 'UNKNOWN';
		
    /*
    |--------------------------------------------------------------------------
    | Research Metadata
    |--------------------------------------------------------------------------
    */

    $timestamp = $history[$i]['epoch'] ?? time();
    $hour      = (int)date('G', $timestamp);
    $session   = 'OVERLAP';

if($hour>=0 && $hour<6){

    $session='ASIA';

}
elseif($hour>=6 && $hour<12){

    $session='EUROPE';

}
elseif($hour>=12 && $hour<18){

    $session='US';

}
else{

    $session='OVERLAP';

}

    foreach(['CALL','PUT'] as $direction){

        for($duration=1; $duration<=5; $duration++){

            if(!isset($history[$i+$duration])){
                continue;
            }

$entryPrice = (float)$t4;

$priceString = preg_replace(
    '/\D/',
    '',
    number_format($entryPrice,3,'.','')
);

$digit = substr($priceString,-1);

$last2 = substr($priceString,-2);

            $exitPrice = $history[$i+$duration]['quote'];

            $key = implode('|',[
                $pattern,
                $direction,
                $duration,
                $rsiZone,
                $trend,
                $marketState,
                $digit,
                $last2,
                $session
            ]);

            if(!isset($knowledge[$key])){
                $knowledge[$key] = [
                    'pattern'        => $pattern,
                    'direction'      => $direction,
                    'duration'       => $duration,
                    'rsi_zone'       => $rsiZone,
                    'trend'          => $trend,
                    'market_state'   => $marketState,
                    'digit'          => $digit,
                    'last2'          => $last2,
                    'session'        => $session,
                    'first_seen'     => date('Y-m-d'),
                    'last_seen'      => date('Y-m-d'),
                    'lifecycle'      => 'DISCOVERED',
                    'survival'       => 'UNPROVEN',
                    'trust'          => 0,
                    'research_score' => 0,
                    'win'            => 0,
                    'loss'           => 0,
                    'samples'        => 0
                ];
            }

            $result = 'LOSS';

            if($direction === 'CALL' && $exitPrice > $entryPrice){
                $result = 'WIN';
            }

            if($direction === 'PUT' && $exitPrice < $entryPrice){
                $result = 'WIN';
            }

            $knowledge[$key]['samples']++;

            if($result === 'WIN'){
                $knowledge[$key]['win']++;
            } else {
                $knowledge[$key]['loss']++;
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Calculate WR & Trust Metrics (FIXED LOOP)
|--------------------------------------------------------------------------
*/

foreach($knowledge as &$row){

    $total = $row['win'] + $row['loss'];

    $row['wr'] = $total > 0 ? round(($row['win'] / $total) * 100, 2) : 0;

    $row['trust'] = round(
        ($row['wr'] * 0.7) + (min($row['samples'], 100) / 100 * 30),
        2
    );

    if($row['trust'] >= 75 && $row['samples'] >= 50){
        $row['survival']  = 'SURVIVOR';
        $row['lifecycle'] = 'SURVIVOR';
    } elseif($row['trust'] >= 65){
        $row['survival']  = 'CANDIDATE';
        $row['lifecycle'] = 'CANDIDATE';
    } else {
        $row['survival']  = 'UNPROVEN';
    }
}
unset($row); // Memutus referensi &row aman

/*
|--------------------------------------------------------------------------
| Filter Tiny Samples
|--------------------------------------------------------------------------
*/

foreach($knowledge as $key => $row){
    if($row['samples'] < 20){
        unset($knowledge[$key]);
    }
}

/*
|--------------------------------------------------------------------------
| Sort Best First (FIXED MULTI-SORT)
|--------------------------------------------------------------------------
*/

uasort($knowledge, function($a, $b) {
    // Urutkan berdasarkan WR terbesar, jika sama urutkan berdasarkan TRUST terbesar
    return ($b['wr'] <=> $a['wr']) ?: ($b['trust'] <=> $a['trust']);
});

/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

file_put_contents(
    dirname(__DIR__).'/storage/context_knowledge_v2.json',
    json_encode($knowledge, JSON_PRETTY_PRINT)
);

echo 'CONTEXT KNOWLEDGE V2 UPDATED';