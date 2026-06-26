<?php

/*
|--------------------------------------------------------------------------
| AARM
| Brain Performance Tracker V1
|--------------------------------------------------------------------------
*/

$consensusFile =
    dirname(__DIR__).'/storage/brain_consensus_v3.json';

$feedbackFile =
    dirname(__DIR__).'/storage/manual_feedback.json';

$outputFile =
    dirname(__DIR__).'/storage/brain_performance.json';

if(
    !file_exists($consensusFile)
    ||
    !file_exists($feedbackFile)
){
    exit('DATA NOT READY');
}

$consensus =
    json_decode(
        file_get_contents($consensusFile),
        true
    ) ?: [];

$feedbacks =
    json_decode(
        file_get_contents($feedbackFile),
        true
    ) ?: [];

if(
    count($feedbacks) === 0
){
    exit('NO FEEDBACK');
}

$lastFeedback =
    end($feedbacks);

$result =
    strtoupper(
        $lastFeedback['result']
        ?? ''
    );

if(
    !in_array(
        $result,
        ['WIN','LOSS']
    )
){
    exit('INVALID FEEDBACK');
}

$stats = [];

if(file_exists($outputFile)){

    $stats =
        json_decode(
            file_get_contents($outputFile),
            true
        ) ?: [];

}

/*
|--------------------------------------------------------------------------
| Process Brain Votes
|--------------------------------------------------------------------------
*/

foreach(
    ($consensus['brains'] ?? [])
    as $brain
){

    $brainName =
        strtoupper(
            $brain['brain']
            ?? 'UNKNOWN'
        );

    if(
        !isset(
            $stats[$brainName]
        )
    ){

        $stats[$brainName] = [

            'win' => 0,
            'loss' => 0,
            'total' => 0,
            'wr' => 50

        ];

    }

    if($result === 'WIN'){

        $stats[$brainName]['win']++;

    }

    if($result === 'LOSS'){

        $stats[$brainName]['loss']++;

    }

    $stats[$brainName]['total'] =

        $stats[$brainName]['win']
        +
        $stats[$brainName]['loss'];

    if(
        $stats[$brainName]['total']
        > 0
    ){

        $stats[$brainName]['wr'] =

            round(

                (
                    $stats[$brainName]['win']
                    /
                    $stats[$brainName]['total']
                ) * 100,

                2

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
        $stats,
        JSON_PRETTY_PRINT
    )

);

echo
    'BRAIN PERFORMANCE UPDATED';