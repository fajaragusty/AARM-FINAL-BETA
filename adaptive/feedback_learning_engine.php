<?php

error_reporting(E_ALL);

/*
|--------------------------------------------------------------------------
| Files
|--------------------------------------------------------------------------
*/

$feedbackFile =
    dirname(__DIR__).'/storage/manual_feedback.json';

$experienceFile =
    dirname(__DIR__).'/storage/experience_v2.json';

/*
|--------------------------------------------------------------------------
| Load Data
|--------------------------------------------------------------------------
*/

$feedbacks = [];

if(file_exists($feedbackFile)){

    $feedbacks =
        json_decode(
            file_get_contents(
                $feedbackFile
            ),
            true
        ) ?: [];

}

$experience = [];

/*
|--------------------------------------------------------------------------
| Rebuild Experience Matrix
|--------------------------------------------------------------------------
*/

foreach($feedbacks as $row){

    $pattern =
        $row['pattern']
        ?? '';

    $direction =
        $row['direction']
        ?? '';

    $result =
        strtoupper(
            $row['result']
            ?? ''
        );

    if(
        !$pattern
        ||
        !$direction
    ){
        continue;
    }

    $keys = [

        'PATTERN:'.$pattern,

        'PATTERN_DIR:'
        .$pattern
        .'|'
        .$direction,

        'DIGIT:'
        .($row['digit'] ?? ''),

        'LAST2:'
        .($row['last2'] ?? ''),

        'TREND:'
        .($row['trend'] ?? ''),

        'MARKET:'
        .($row['market_state'] ?? ''),

        'PATTERN_TREND:'
        .$pattern
        .'|'
        .($row['trend'] ?? ''),

        'PATTERN_MARKET:'
        .$pattern
        .'|'
        .($row['market_state'] ?? ''),

        'PATTERN_DIGIT:'
        .$pattern
        .'|'
        .($row['digit'] ?? '')

    ];

    foreach($keys as $key){

        if(
            !isset(
                $experience[$key]
            )
        ){

            $experience[$key] = [

                'key' =>
                    $key,

                'win' => 0,

                'loss' => 0,

                'total' => 0,

                'trust' => 50

            ];

        }

        if($result === 'WIN'){

            $experience[$key]['win']++;

        }

        if($result === 'LOSS'){

            $experience[$key]['loss']++;

        }

    }

}

/*
|--------------------------------------------------------------------------
| Calculate Trust
|--------------------------------------------------------------------------
*/

foreach($experience as &$row){

    $total =
        $row['win']
        +
        $row['loss'];

    $row['total'] =
        $total;

    if($total > 0){

        $row['trust'] =
            round(
                (
                    $row['win']
                    /
                    $total
                ) * 100,
                2
            );

    }else{

        $row['trust'] = 50;

    }

}

unset($row);

/*
|--------------------------------------------------------------------------
| Sort By Trust
|--------------------------------------------------------------------------
*/

uasort(

    $experience,

    function($a,$b){

        return
            $b['trust']
            <=>
            $a['trust'];

    }

);

/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

file_put_contents(

    $experienceFile,

    json_encode(
        $experience,
        JSON_PRETTY_PRINT
    )

);

/*
|--------------------------------------------------------------------------
| Console
|--------------------------------------------------------------------------
*/

echo PHP_EOL;

echo "=================================\n";
echo " EXPERIENCE MATRIX V3\n";
echo "=================================\n";

echo "Feedback : "
    .count($feedbacks)
    ."\n";

echo "Knowledge : "
    .count($experience)
    ."\n";

echo "Saved : experience_v2.json\n";

echo "=================================\n";