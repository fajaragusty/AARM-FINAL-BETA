<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA
| Learning Feedback Engine V1
|--------------------------------------------------------------------------
|
| Reward / Punish knowledge berdasarkan trade_journal
|
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);

$journalFile =
    dirname(__DIR__).'/storage/trade_journal.json';

$knowledgeFile =
    dirname(__DIR__).'/storage/knowledge_status.json';

$outputLog =
    dirname(__DIR__).'/storage/learning_feedback_log.json';

if(!file_exists($journalFile)){
    exit('trade_journal.json NOT FOUND');
}

if(!file_exists($knowledgeFile)){
    exit('knowledge_status.json NOT FOUND');
}

$journal =
    json_decode(
        file_get_contents($journalFile),
        true
    ) ?: [];

$knowledge =
    json_decode(
        file_get_contents($knowledgeFile),
        true
    ) ?: [];

$changes = [];

foreach($journal as $trade){

    /*
    |--------------------------------------------------------------------------
    | Skip pending
    |--------------------------------------------------------------------------
    */

    if(
        !isset($trade['result'])
    ){
        continue;
    }

    if(
        $trade['result']
        === 'PENDING'
    ){
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | Already processed?
    |--------------------------------------------------------------------------
    */

    if(
        isset(
            $trade['feedback_processed']
        )
        &&
        $trade['feedback_processed']
    ){
        continue;
    }

    $patternKey =
        $trade['pattern_key']
        ?? '';

    if(
        empty($patternKey)
    ){
        continue;
    }

    if(
        !isset(
            $knowledge[$patternKey]
        )
    ){
        continue;
    }

    $oldScore =
        $knowledge[$patternKey]['score']
        ?? 50;

    $newScore =
        $oldScore;

    /*
    |--------------------------------------------------------------------------
    | Reward / Punish
    |--------------------------------------------------------------------------
    */

    if(
        $trade['result']
        === 'WIN'
    ){

        $newScore += 1;

    }else{

        $newScore -= 1;

    }

    /*
    |--------------------------------------------------------------------------
    | Clamp
    |--------------------------------------------------------------------------
    */

    if($newScore > 100){
        $newScore = 100;
    }

    if($newScore < 0){
        $newScore = 0;
    }

    $knowledge[$patternKey]['score'] =
        round(
            $newScore,
            2
        );

    $changes[] = [

        'time' =>
            date('Y-m-d H:i:s'),

        'trade_id' =>
            $trade['id']
            ?? '',

        'pattern_key' =>
            $patternKey,

        'result' =>
            $trade['result'],

        'old_score' =>
            $oldScore,

        'new_score' =>
            $newScore

    ];

}

/*
|--------------------------------------------------------------------------
| Mark processed trades
|--------------------------------------------------------------------------
*/

foreach($journal as &$trade){

    if(
        isset(
            $trade['feedback_processed']
        )
        &&
        $trade['feedback_processed']
    ){
        continue;
    }

    if(
        isset($trade['result'])
        &&
        $trade['result']
        !== 'PENDING'
    ){

        $trade['feedback_processed']
            = true;

        $trade['feedback_time']
            = date(
                'Y-m-d H:i:s'
            );

    }

}

unset($trade);

/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

file_put_contents(

    $knowledgeFile,

    json_encode(
        $knowledge,
        JSON_PRETTY_PRINT
    )

);

file_put_contents(

    $journalFile,

    json_encode(
        $journal,
        JSON_PRETTY_PRINT
    )

);

file_put_contents(

    $outputLog,

    json_encode(
        $changes,
        JSON_PRETTY_PRINT
    )

);

echo PHP_EOL;
echo "=================================".PHP_EOL;
echo "LEARNING FEEDBACK ENGINE".PHP_EOL;
echo "=================================".PHP_EOL;
echo "Updated : "
    .count($changes)
    ." knowledge".PHP_EOL;
echo "Log File: learning_feedback_log.json".PHP_EOL;
echo "=================================".PHP_EOL;
echo PHP_EOL;