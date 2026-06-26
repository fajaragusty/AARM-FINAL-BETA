<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA
| Last2 Score Engine V1
|--------------------------------------------------------------------------
*/

$sourceFile =
    dirname(__DIR__).'/storage/last2_knowledge.json';

$outputFile =
    dirname(__DIR__).'/storage/last2_scores.json';

if(!file_exists($sourceFile)){
    exit('last2_knowledge.json NOT FOUND');
}

$data = json_decode(
    file_get_contents($sourceFile),
    true
) ?: [];

if(empty($data)){
    exit('NO LAST2 DATA');
}

$result = [];

foreach($data as $key => $row){

    $wr =
        $row['wr']
        ?? 0;

    $samples =
        $row['samples']
        ?? 0;

    /*
    |--------------------------------------------------------------------------
    | Sample Factor
    |--------------------------------------------------------------------------
    */

    $sampleFactor =
        min(
            $samples / 100,
            1
        );

    /*
    |--------------------------------------------------------------------------
    | Score Formula
    |--------------------------------------------------------------------------
    */

    $score = round(

        ($wr * 0.70)

        +

        ($sampleFactor * 30),

        2

    );

    /*
    |--------------------------------------------------------------------------
    | Tier
    |--------------------------------------------------------------------------
    */

    if($score >= 75){

        $tier = 'A';

    }
    elseif($score >= 65){

        $tier = 'B';

    }
    elseif($score >= 55){

        $tier = 'C';

    }
    else{

        $tier = 'D';

    }

    $result[$key] = [

        'last2' =>
            $row['last2'],

        'direction' =>
            $row['direction'],

        'duration' =>
            $row['duration'],

        'win' =>
            $row['win'],

        'loss' =>
            $row['loss'],

        'samples' =>
            $samples,

        'wr' =>
            $wr,

        'sample_factor' =>
            round(
                $sampleFactor,
                2
            ),

        'score' =>
            $score,

        'tier' =>
            $tier

    ];

}

/*
|--------------------------------------------------------------------------
| Sort by Score
|--------------------------------------------------------------------------
*/

uasort(

    $result,

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

        $result,

        JSON_PRETTY_PRINT

    )

);

echo 'LAST2 SCORE UPDATED';