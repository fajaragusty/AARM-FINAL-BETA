<?php

$ticksFile =
    dirname(__DIR__).'/storage/ticks.json';

$historyFile =
    dirname(__DIR__).'/storage/historical_ticks.json';

$ticks = json_decode(
    file_get_contents($ticksFile),
    true
) ?: [];

if(count($ticks) < 30){
    exit('NOT ENOUGH TICKS');
}

/*
|--------------------------------------------------------------------------
| Load Existing History
|--------------------------------------------------------------------------
*/

$history = [];

if(file_exists($historyFile)){

    $history = json_decode(
        file_get_contents($historyFile),
        true
    ) ?: [];

}

/*
|--------------------------------------------------------------------------
| Build Enriched History
|--------------------------------------------------------------------------
*/

foreach($ticks as $i => $tick){

    if($i < 21){
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate
    |--------------------------------------------------------------------------
    */

    $exists = false;

    foreach($history as $row){

        if(
            ($row['epoch'] ?? 0)
            ==
            ($tick['epoch'] ?? 0)
        ){
            $exists = true;
            break;
        }

    }

    if($exists){
        continue;
    }

    $prices = [];

    for(
        $j = max(0,$i-30);
        $j <= $i;
        $j++
    ){

        $prices[] =
            $ticks[$j]['quote'];

    }

    /*
    |--------------------------------------------------------------------------
    | EMA
    |--------------------------------------------------------------------------
    */

    $ema9 =
        array_sum(
            array_slice(
                $prices,
                -9
            )
        ) / 9;

    $ema21 =
        array_sum(
            array_slice(
                $prices,
                -21
            )
        ) / 21;

    /*
    |--------------------------------------------------------------------------
    | RSI
    |--------------------------------------------------------------------------
    */

    $gains = [];
    $losses = [];

    for(
        $k=1;
        $k<count($prices);
        $k++
    ){

        $diff =
            $prices[$k]
            -
            $prices[$k-1];

        if($diff > 0){

            $gains[] = $diff;

        }else{

            $losses[] = abs($diff);

        }

    }

    $avgGain =
        count($gains)
        ?
        array_sum($gains)
        /
        count($gains)
        :
        0;

    $avgLoss =
        count($losses)
        ?
        array_sum($losses)
        /
        count($losses)
        :
        0;

    $rsi = 50;

    if($avgLoss > 0){

        $rs =
            $avgGain
            /
            $avgLoss;

        $rsi =
            100
            -
            (
                100
                /
                (1+$rs)
            );

    }

    /*
    |--------------------------------------------------------------------------
    | Trend
    |--------------------------------------------------------------------------
    */

    $trend = 'SIDEWAYS';

    if($ema9 > $ema21){

        $trend = 'BULLISH';

    }
    elseif($ema9 < $ema21){

        $trend = 'BEARISH';

    }

    /*
    |--------------------------------------------------------------------------
    | Momentum
    |--------------------------------------------------------------------------
    */

    $momentum = 'FLAT';

    if(
        $prices[count($prices)-1]
        >
        $prices[count($prices)-2]
    ){

        $momentum = 'UP';

    }
    elseif(
        $prices[count($prices)-1]
        <
        $prices[count($prices)-2]
    ){

        $momentum = 'DOWN';

    }

    /*
    |--------------------------------------------------------------------------
    | Market State
    |--------------------------------------------------------------------------
    */

    $marketState = 'RANGING';

    if(
        abs($ema9-$ema21)
        > 0.5
    ){

        $marketState =
            'TRENDING';

    }

    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    $history[] = [

        'quote' =>
            $tick['quote'],

        'epoch' =>
            $tick['epoch'],

        'rsi' =>
            round($rsi,2),

        'ema9' =>
            round($ema9,5),

        'ema21' =>
            round($ema21,5),

        'trend' =>
            $trend,

        'momentum' =>
            $momentum,

        'market_state' =>
            $marketState

    ];

}

/*
|--------------------------------------------------------------------------
| Keep Last 10000
|--------------------------------------------------------------------------
*/

if(count($history) > 10000){

    $history = array_slice(
        $history,
        -10000
    );

}

file_put_contents(

    $historyFile,

    json_encode(
        $history,
        JSON_PRETTY_PRINT
    )

);

echo 'HISTORICAL MEMORY UPDATED';