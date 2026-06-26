<?php

$ticks = json_decode(
    file_get_contents(
        dirname(__DIR__).'/storage/ticks.json'
    ),
    true
) ?: [];

if(count($ticks) < 30){
    exit('NOT ENOUGH TICKS');
}

/*
|--------------------------------------------------------------------------
| Extract Prices
|--------------------------------------------------------------------------
*/

$prices = array_column(
    $ticks,
    'quote'
);

/*
|--------------------------------------------------------------------------
| EMA Function
|--------------------------------------------------------------------------
*/

function ema($prices, $period){

    $count = count($prices);

    if($count < $period){
        return 0;
    }

    $slice = array_slice(
        $prices,
        -$period
    );

    $ema = array_sum($slice) / $period;

    $multiplier =
        2 / ($period + 1);

    foreach($slice as $price){

        $ema =
            (($price - $ema)
            * $multiplier)
            + $ema;

    }

    return round(
        $ema,
        5
    );
}

/*
|--------------------------------------------------------------------------
| RSI 14
|--------------------------------------------------------------------------
*/

function rsi($prices, $period = 14){

    if(count($prices) < $period+1){
        return 50;
    }

    $gains = [];
    $losses = [];

    for(
        $i=count($prices)-$period;
        $i<count($prices);
        $i++
    ){

        $change =
            $prices[$i]
            -
            $prices[$i-1];

        if($change > 0){

            $gains[] = $change;

        }else{

            $losses[] = abs($change);

        }

    }

    $avgGain =
        count($gains)
        ?
        array_sum($gains)/count($gains)
        :
        0;

    $avgLoss =
        count($losses)
        ?
        array_sum($losses)/count($losses)
        :
        0;

    if($avgLoss == 0){
        return 100;
    }

    $rs =
        $avgGain / $avgLoss;

    return round(
        100 -
        (
            100 /
            (1+$rs)
        ),
        2
    );
}

/*
|--------------------------------------------------------------------------
| Calculate Indicators
|--------------------------------------------------------------------------
*/

$ema9  = ema($prices,9);
$ema21 = ema($prices,21);

$rsi14 = rsi($prices,14);

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

$last =
    end($prices);

$prev =
    $prices[
        count($prices)-2
    ];

if($last > $prev){

    $momentum = 'UP';

}
elseif($last < $prev){

    $momentum = 'DOWN';

}

/*
|--------------------------------------------------------------------------
| Market State
|--------------------------------------------------------------------------
*/

$marketState = 'RANGING';

if(
    abs(
        $ema9 - $ema21
    ) > 0.5
){

    $marketState =
        'TRENDING';

}

/*
|--------------------------------------------------------------------------
| Save Context
|--------------------------------------------------------------------------
*/

$context = [

    'time' =>
        time(),

    'rsi' =>
        $rsi14,

    'ema9' =>
        $ema9,

    'ema21' =>
        $ema21,

    'trend' =>
        $trend,

    'momentum' =>
        $momentum,

    'market_state' =>
        $marketState

];

file_put_contents(

    dirname(__DIR__).'/storage/context.json',

    json_encode(
        $context,
        JSON_PRETTY_PRINT
    )

);

echo 'CONTEXT UPDATED';