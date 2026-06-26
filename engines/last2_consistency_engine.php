<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA
| Last2 Consistency Engine V1
|--------------------------------------------------------------------------
*/

$sourceFile =
    dirname(__DIR__).'/storage/last2_scores.json';

$outputFile =
    dirname(__DIR__).'/storage/last2_consistency.json';

if(!file_exists($sourceFile)){
    exit('last2_scores.json NOT FOUND');
}

$data = json_decode(
    file_get_contents($sourceFile),
    true
) ?: [];

if(empty($data)){
    exit('NO LAST2 SCORE DATA');
}

$grouped = [];

/*
|--------------------------------------------------------------------------
| Group by LAST2
|--------------------------------------------------------------------------
*/

foreach($data as $key => $row){

    $last2 =
        $row['last2']
        ?? null;

    if(!$last2){
        continue;
    }

    if(!isset($grouped[$last2])){

        $grouped[$last2] = [

            'last2' => $last2,

            'scores' => [],

            'durations' => [],

            'directions' => []

        ];

    }

    $grouped[$last2]['scores'][] =
        $row['score'];

    $grouped[$last2]['durations'][] =
        $row['duration'];

    $grouped[$last2]['directions'][] =
        $row['direction'];

}

/*
|--------------------------------------------------------------------------
| Consistency Analysis
|--------------------------------------------------------------------------
*/

$result = [];

foreach($grouped as $last2 => $info){

    $appearances =
        count(
            array_unique(
                $info['durations']
            )
        );

    $avgScore =
        round(
            array_sum(
                $info['scores']
            )
            /
            count(
                $info['scores']
            ),
            2
        );

    /*
    |--------------------------------------------------------------------------
    | Consistency Formula
    |--------------------------------------------------------------------------
    */

    $consistencyScore = round(

        $avgScore

        +

        ($appearances * 5),

        2

    );

    /*
    |--------------------------------------------------------------------------
    | Rank
    |--------------------------------------------------------------------------
    */

    if($consistencyScore >= 75){

        $rank = 'STRONG';

    }
    elseif($consistencyScore >= 65){

        $rank = 'GOOD';

    }
    elseif($consistencyScore >= 55){

        $rank = 'OBSERVE';

    }
    else{

        $rank = 'WEAK';

    }

    $result[$last2] = [

        'last2' =>
            $last2,

        'appearances' =>
            $appearances,

        'avg_score' =>
            $avgScore,

        'consistency_score' =>
            $consistencyScore,

        'rank' =>
            $rank

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

            $b['consistency_score']

            <=>

            $a['consistency_score'];

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

echo 'LAST2 CONSISTENCY UPDATED';