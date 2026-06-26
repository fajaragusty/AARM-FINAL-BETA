<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA
| Knowledge Timeline Engine V1
|--------------------------------------------------------------------------
*/

$sourceFile =
    dirname(__DIR__).'/storage/knowledge_scores.json';

$outputFile =
    dirname(__DIR__).'/storage/knowledge_timeline.json';

if(!file_exists($sourceFile)){
    exit('knowledge_scores.json NOT FOUND');
}

$current = json_decode(
    file_get_contents($sourceFile),
    true
) ?: [];

if(empty($current)){
    exit('NO KNOWLEDGE FOUND');
}

/*
|--------------------------------------------------------------------------
| Load Existing Timeline
|--------------------------------------------------------------------------
*/

$timeline = [];

if(file_exists($outputFile)){

    $timeline = json_decode(
        file_get_contents($outputFile),
        true
    ) ?: [];

}

$timestamp =
    date('Y-m-d H:i:s');

/*
|--------------------------------------------------------------------------
| Append Snapshot
|--------------------------------------------------------------------------
*/

foreach($current as $key => $row){

    if(!isset($timeline[$key])){

        $timeline[$key] = [];

    }

    $timeline[$key][] = [

        'timestamp' =>
            $timestamp,

        'score' =>
            $row['score']
            ?? 0,

        'wr' =>
            $row['wr']
            ?? 0,

        'samples' =>
            $row['samples']
            ?? 0

    ];

    /*
    |--------------------------------------------------------------------------
    | Keep Last 100 Snapshots
    |--------------------------------------------------------------------------
    */

    if(
        count(
            $timeline[$key]
        ) > 100
    ){

        $timeline[$key] =
            array_slice(
                $timeline[$key],
                -100
            );

    }

}

/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

file_put_contents(

    $outputFile,

    json_encode(

        $timeline,

        JSON_PRETTY_PRINT

    )

);

echo 'KNOWLEDGE TIMELINE UPDATED';