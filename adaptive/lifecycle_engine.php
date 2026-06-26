<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA
| Lifecycle Engine V1
|--------------------------------------------------------------------------
*/

$sourceFile =
    dirname(__DIR__).'/storage/knowledge_scores.json';

$outputFile =
    dirname(__DIR__).'/storage/knowledge_lifecycle.json';

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

    $status = 'GRAVEYARD';

    /*
    |--------------------------------------------------------------------------
    | ACTIVE
    |--------------------------------------------------------------------------
    */

    if(
        $score >= 55
        &&
        $samples >= 50
    ){

        $status = 'ACTIVE';

    }

    /*
    |--------------------------------------------------------------------------
    | DECAYING
    |--------------------------------------------------------------------------
    */

    elseif(
        $score >= 50
    ){

        $status = 'DECAYING';

    }

    /*
    |--------------------------------------------------------------------------
    | DEAD
    |--------------------------------------------------------------------------
    */

    elseif(
        $score >= 45
    ){

        $status = 'DEAD';

    }

    /*
    |--------------------------------------------------------------------------
    | GRAVEYARD
    |--------------------------------------------------------------------------
    */

    else{

        $status = 'GRAVEYARD';

    }

    $result[$key] = [

        'pattern' =>
            $row['pattern']
            ?? '',

        'direction' =>
            $row['direction']
            ?? '',

        'duration' =>
            $row['duration']
            ?? 0,

        'samples' =>
            $samples,

        'wr' =>
            $row['wr']
            ?? 0,

        'score' =>
            $score,

        'status' =>
            $status

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

        $priority = [

            'ACTIVE' => 4,
            'DECAYING' => 3,
            'DEAD' => 2,
            'GRAVEYARD' => 1

        ];

        return

            $priority[$b['status']]
            <=>

            $priority[$a['status']];

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

echo 'LIFECYCLE UPDATED';