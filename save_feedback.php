<?php

header('Content-Type: application/json');

$data = json_decode(
    file_get_contents('php://input'),
    true
);

if(!$data){

    echo json_encode([
        'success' => false,
        'message' => 'Invalid payload'
    ]);

    exit;
}

$result =
    strtoupper(
        $data['result']
        ?? ''
    );

if(
    !in_array(
        $result,
        ['WIN','LOSS']
    )
){

    echo json_encode([
        'success' => false,
        'message' => 'Invalid result'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Manual Feedback File
|--------------------------------------------------------------------------
*/

$file =
    __DIR__
    .'/storage/manual_feedback.json';

$logs = [];

if(file_exists($file)){

    $logs =
        json_decode(
            file_get_contents($file),
            true
        ) ?: [];

}

/*
|--------------------------------------------------------------------------
| Load Paper Trades
|--------------------------------------------------------------------------
*/

$paperTradesFile =
    __DIR__
    .'/storage/paper_trades.json';

$paperTrades = [];

if(file_exists($paperTradesFile)){

    $paperTrades =
        json_decode(
            file_get_contents(
                $paperTradesFile
            ),
            true
        ) ?: [];

}

/*
|--------------------------------------------------------------------------
| Find Trade By Signal Time
|--------------------------------------------------------------------------
*/

$trade =
    end(
        $paperTrades
    );

/*
|--------------------------------------------------------------------------
| Fallback
|--------------------------------------------------------------------------
*/

if(!$trade){

    echo json_encode([
        'success' => false,
        'message' => 'Trade not found'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Save Feedback
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| Brain Snapshot
|--------------------------------------------------------------------------
*/

$brainConsensus = [];

$brainFile =
    __DIR__
    .'/storage/brain_consensus_v3.json';

if(file_exists($brainFile)){

    $brainConsensus =
        json_decode(
            file_get_contents($brainFile),
            true
        ) ?: [];

}

$brains =
    $brainConsensus['brains']
    ?? [];

$winnerSide =
    $brainConsensus['winner_side']
    ?? '';

$decision =
    $brainConsensus['decision']
    ?? '';

$brainConfidence =
    $brainConsensus['confidence']
    ?? 0;
	
$logs[] = [

    'time' =>
        date('Y-m-d H:i:s'),

    'timestamp' =>
        time(),

'signal_time' =>
    $trade['signal_time']
    ?? 0,

    'pattern' =>
        $trade['pattern']
        ?? '',

    'direction' =>
        $trade['direction']
        ?? '',

    'digit' =>
        $trade['digit']
        ?? '',

    'last2' =>
        $trade['last2']
        ?? '',

    'hour' =>
        $trade['hour']
        ?? '',

    'rsi' =>
        $trade['rsi']
        ?? 50,

    'trend' =>
        $trade['trend']
        ?? 'SIDEWAYS',

    'momentum' =>
        $trade['momentum']
        ?? 'FLAT',

    'market_state' =>
        $trade['market_state']
        ?? 'RANGING',

    'ema9' =>
        $trade['ema9']
        ?? 0,

    'ema21' =>
        $trade['ema21']
        ?? 0,

    'confidence' =>
        $trade['confidence']
        ?? 0,

    'entry_price' =>
        $trade['entry_price']
        ?? 0,

'result' =>
    $result,

'brain_decision' =>
    $decision,

'winner_side' =>
    $winnerSide,

'brain_confidence' =>
    $brainConfidence,

'brains' =>
    $brains

];

file_put_contents(

    $file,

    json_encode(
        $logs,
        JSON_PRETTY_PRINT
    )

);

echo json_encode([

    'success' => true,

    'message' =>
        'Feedback saved : '
        .$result

]);