<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA
| Trade Journal Engine V1
|--------------------------------------------------------------------------
| Menyimpan keputusan consensus ke jurnal
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);

$consensusFile =
    dirname(__DIR__).'/storage/brain_consensus_v3.json';

$journalFile =
    dirname(__DIR__).'/storage/trade_journal.json';

if(!file_exists($consensusFile)){
    exit('brain_consensus_v3.json NOT FOUND');
}

$consensus =
    json_decode(
        file_get_contents($consensusFile),
        true
    ) ?: [];

$journal = [];

if(file_exists($journalFile)){

    $journal =
        json_decode(
            file_get_contents($journalFile),
            true
        ) ?: [];

}

/*
|--------------------------------------------------------------------------
| Extract Data
|--------------------------------------------------------------------------
*/

$decision =
    $consensus['decision']
    ?? 'WAIT';

$confidence =
    $consensus['confidence']
    ?? 0;

$winnerSide =
    $consensus['winner_side']
    ?? 'UNKNOWN';

$reason =
    $consensus['consensus_reason']
    ?? 'UNKNOWN';

$patternBrain =
    $consensus['brains'][0]
    ?? [];

$digitBrain =
    $consensus['brains'][1]
    ?? [];

/*
|--------------------------------------------------------------------------
| Create Journal Entry
|--------------------------------------------------------------------------
*/

$entry = [

    'id' =>
        uniqid('TRD_'),

    'time' =>
        date('Y-m-d H:i:s'),

    'decision' =>
        $decision,

    'confidence' =>
        $confidence,

    'winner_side' =>
        $winnerSide,

    'consensus_reason' =>
        $reason,

    /*
    |--------------------------------------------------------------------------
    | Pattern Brain Snapshot
    |--------------------------------------------------------------------------
    */

    'pattern_key' =>
        $patternBrain['key']
        ?? '',

    'pattern_vote' =>
        $patternBrain['vote']
        ?? '',

    'pattern_score' =>
        $patternBrain['score']
        ?? 0,

    'pattern_status' =>
        $patternBrain['status']
        ?? '',

    /*
    |--------------------------------------------------------------------------
    | Digit Brain Snapshot
    |--------------------------------------------------------------------------
    */

    'digit_key' =>
        $digitBrain['key']
        ?? '',

    'digit_vote' =>
        $digitBrain['vote']
        ?? '',

    'digit_score' =>
        $digitBrain['score']
        ?? 0,

    'digit_status' =>
        $digitBrain['status']
        ?? '',

    /*
    |--------------------------------------------------------------------------
    | Trade Result
    |--------------------------------------------------------------------------
    */

    'result' =>
        'PENDING',

    'profit' =>
        0,

    'notes' =>
        ''

];

$journal[] = $entry;

/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

file_put_contents(

    $journalFile,

    json_encode(
        $journal,
        JSON_PRETTY_PRINT
    )

);

echo PHP_EOL;
echo "==========================".PHP_EOL;
echo "TRADE JOURNAL ENGINE".PHP_EOL;
echo "==========================".PHP_EOL;
echo "Journal ID : ".$entry['id'].PHP_EOL;
echo "Decision   : ".$decision.PHP_EOL;
echo "Confidence : ".$confidence."%".PHP_EOL;
echo "Result     : PENDING".PHP_EOL;
echo "==========================".PHP_EOL;
echo PHP_EOL;