<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA
| Knowledge Status Engine V1
|--------------------------------------------------------------------------
*/

$knowledgeFile =
    dirname(__DIR__).'/storage/knowledge_scores.json';

$lifecycleFile =
    dirname(__DIR__).'/storage/knowledge_lifecycle.json';

$confidenceFile =
    dirname(__DIR__).'/storage/confidence_scores.json';

$outputFile =
    dirname(__DIR__).'/storage/knowledge_status.json';

foreach([
    $knowledgeFile,
    $lifecycleFile,
    $confidenceFile
] as $file){

    if(!file_exists($file)){
        exit(
            basename($file)
            .' NOT FOUND'
        );
    }

}

$knowledge =
    json_decode(
        file_get_contents($knowledgeFile),
        true
    ) ?: [];

$lifecycle =
    json_decode(
        file_get_contents($lifecycleFile),
        true
    ) ?: [];

$confidence =
    json_decode(
        file_get_contents($confidenceFile),
        true
    ) ?: [];

$result = [];

foreach($knowledge as $key => $row){

    $score =
        $row['score']
        ?? 0;

    $samples =
        $row['samples']
        ?? 0;

    $wr =
        $row['wr']
        ?? 0;

    $confidenceScore = 0;

    if(
        isset(
            $confidence[$key]
        )
    ){

        $confidenceScore =
            $confidence[$key]
            ['confidence_score']
            ?? 0;

    }

    /*
    |--------------------------------------------------------------------------
    | Status Logic
    |--------------------------------------------------------------------------
    */

    $status = 'UNPROVEN';

    if(
        $score >= 55
        &&
        $samples >= 100
        &&
        $confidenceScore >= 50
    ){

        $status = 'ACTIVE';

    }
    elseif(
        $score >= 55
    ){

        $status = 'UNPROVEN';

    }
    elseif(
        $score >= 50
    ){

        $status = 'DECAYING';

    }
    elseif(
        $score >= 45
    ){

        $status = 'DEAD';

    }
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
            $wr,

        'score' =>
            $score,

        'confidence_score' =>
            round(
                $confidenceScore,
                2
            ),

        'status' =>
            $status

    ];

}

/*
|--------------------------------------------------------------------------
| Status Priority Sort
|--------------------------------------------------------------------------
*/

$priority = [

    'ACTIVE'     => 5,
    'UNPROVEN'   => 4,
    'DECAYING'   => 3,
    'DEAD'       => 2,
    'GRAVEYARD'  => 1

];

uasort(

    $result,

    function($a,$b)
    use($priority){

        return

            $priority[
                $b['status']
            ]

            <=>

            $priority[
                $a['status']
            ];

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

echo 'KNOWLEDGE STATUS UPDATED';