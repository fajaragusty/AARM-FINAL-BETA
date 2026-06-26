<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA
| Graveyard Engine V1
|--------------------------------------------------------------------------
*/

$sourceFile =
    dirname(__DIR__).'/storage/knowledge_lifecycle.json';

$outputFile =
    dirname(__DIR__).'/storage/graveyard.json';

if(!file_exists($sourceFile)){
    exit('knowledge_lifecycle.json NOT FOUND');
}

$data = json_decode(
    file_get_contents($sourceFile),
    true
) ?: [];

if(empty($data)){
    exit('NO LIFECYCLE DATA');
}

$graveyard = [];

foreach($data as $key => $row){

    $status =
        $row['status']
        ?? '';

    $score =
        $row['score']
        ?? 0;

    if(

        $status === 'GRAVEYARD'

        ||

        $score < 45

    ){

        $graveyard[$key] = [

            'buried_at' =>
                date('Y-m-d H:i:s'),

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
                $row['samples']
                ?? 0,

            'wr' =>
                $row['wr']
                ?? 0,

            'score' =>
                $score,

            'status' =>
                'GRAVEYARD'

        ];

    }

}

/*
|--------------------------------------------------------------------------
| Sort Worst First
|--------------------------------------------------------------------------
*/

uasort(

    $graveyard,

    function($a,$b){

        return
            $a['score']
            <=>
            $b['score'];

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

        $graveyard,

        JSON_PRETTY_PRINT

    )

);

echo 'GRAVEYARD UPDATED';