<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA
| Confidence Decay Engine V1
|--------------------------------------------------------------------------
*/

$sourceFile =
    dirname(__DIR__).'/storage/knowledge_scores.json';

$outputFile =
    dirname(__DIR__).'/storage/confidence_scores.json';

if(!file_exists($sourceFile)){
    exit('knowledge_scores.json NOT FOUND');
}

$data = json_decode(
    file_get_contents($sourceFile),
    true
) ?: [];

if(empty($data)){
    exit('NO KNOWLEDGE FOUND');
}

$result = [];

foreach($data as $key => $row){

    $score =
        $row['score']
        ?? 0;

    $samples =
        $row['samples']
        ?? 0;

    $wr =
        $row['wr']
        ?? 0;

    /*
    |--------------------------------------------------------------------------
    | Confidence Factor
    |--------------------------------------------------------------------------
    */

    $confidenceFactor =
        min(
            $samples / 100,
            1
        );

    /*
    |--------------------------------------------------------------------------
    | Confidence Score
    |--------------------------------------------------------------------------
    */

    $confidenceScore = round(

        $score
        *
        $confidenceFactor,

        2

    );

    /*
    |--------------------------------------------------------------------------
    | Confidence Level
    |--------------------------------------------------------------------------
    */

    if($confidenceScore >= 55){

        $level = 'HIGH';

    }
    elseif($confidenceScore >= 45){

        $level = 'MEDIUM';

    }
    elseif($confidenceScore >= 30){

        $level = 'LOW';

    }
    else{

        $level = 'VERY_LOW';

    }

    $result[$key] = [

        'score' =>
            $score,

        'wr' =>
            $wr,

        'samples' =>
            $samples,

        'confidence_factor' =>
            round(
                $confidenceFactor,
                2
            ),

        'confidence_score' =>
            $confidenceScore,

        'confidence_level' =>
            $level

    ];

}

/*
|--------------------------------------------------------------------------
| Sort
|--------------------------------------------------------------------------
*/

uasort(

    $result,

    function($a,$b){

        return

            $b['confidence_score']

            <=>

            $a['confidence_score'];

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

echo 'CONFIDENCE DECAY UPDATED';