<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA
| Knowledge Score Engine V1
|--------------------------------------------------------------------------
*/

$sourceFile =
    dirname(__DIR__).'/storage/context_knowledge_v2.json';

$outputFile =
    dirname(__DIR__).'/storage/knowledge_scores.json';

if(!file_exists($sourceFile)){
    exit('context_knowledge_v2.json NOT FOUND');
}

$knowledge = json_decode(
    file_get_contents($sourceFile),
    true
) ?: [];

if(empty($knowledge)){
    exit('NO KNOWLEDGE FOUND');
}

$scores = [];

/*
|--------------------------------------------------------------------------
| Calculate Score
|--------------------------------------------------------------------------
*/

foreach($knowledge as $key => $row){

    $wr =
        $row['wr']
        ?? 0;

    $samples =
        $row['samples']
        ?? 0;

    /*
    |--------------------------------------------------------------------------
    | Sample Strength
    |--------------------------------------------------------------------------
    |
    | Max contribution = 30
    |
    */

    $sampleStrength =
        (
            min(
                $samples,
                200
            )
            / 200
        )
        * 30;

    /*
    |--------------------------------------------------------------------------
    | WR Strength
    |--------------------------------------------------------------------------
    |
    | Max contribution = 70
    |
    */

    $wrStrength =
        $wr * 0.7;

    /*
    |--------------------------------------------------------------------------
    | Final Score
    |--------------------------------------------------------------------------
    */

    $score = round(

        $wrStrength
        +
        $sampleStrength,

        2

    );

    $scores[$key] = [

        'pattern' =>
            $row['pattern']
            ?? '',

        'direction' =>
            $row['direction']
            ?? '',

        'duration' =>
            $row['duration']
            ?? 0,

        'rsi_zone' =>
            $row['rsi_zone']
            ?? '',

        'trend' =>
            $row['trend']
            ?? '',

        'market_state' =>
            $row['market_state']
            ?? '',

        'win' =>
            $row['win']
            ?? 0,

        'loss' =>
            $row['loss']
            ?? 0,

        'samples' =>
            $samples,

        'wr' =>
            $wr,

        'sample_strength' =>
            round(
                $sampleStrength,
                2
            ),

        'wr_strength' =>
            round(
                $wrStrength,
                2
            ),

        'score' =>
            $score

    ];

}

/*
|--------------------------------------------------------------------------
| Sort By Score Desc
|--------------------------------------------------------------------------
*/

uasort(

    $scores,

    function($a,$b){

        return
            $b['score']
            <=>
            $a['score'];

    }

);

/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

file_put_contents(

    $outputFile,

    json_encode(

        $scores,

        JSON_PRETTY_PRINT

    )

);

echo 'KNOWLEDGE SCORE UPDATED';