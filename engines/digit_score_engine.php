<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA
| Digit Score Engine V1
|--------------------------------------------------------------------------
*/

$sourceFile =
    dirname(__DIR__).'/storage/digit_knowledge.json';

$outputFile =
    dirname(__DIR__).'/storage/digit_scores.json';

if(!file_exists($sourceFile)){
    exit('digit_knowledge.json NOT FOUND');
}

$data = json_decode(
    file_get_contents($sourceFile),
    true
) ?: [];

if(empty($data)){
    exit('NO DIGIT KNOWLEDGE FOUND');
}

$scores = [];

foreach($data as $key => $row){

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
    | Max 30 points
    |
    */

    $sampleStrength =
        (
            min(
                $samples,
                500
            )
            / 500
        )
        * 30;

    /*
    |--------------------------------------------------------------------------
    | WR Strength
    |--------------------------------------------------------------------------
    |
    | Max 70 points
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

        'digit' =>
            $row['digit']
            ?? '',

        'direction' =>
            $row['direction']
            ?? '',

        'duration' =>
            $row['duration']
            ?? 0,

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
| Sort Desc
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

echo 'DIGIT SCORE UPDATED';