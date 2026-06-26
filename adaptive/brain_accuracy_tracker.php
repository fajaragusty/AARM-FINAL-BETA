<?php

/*
|--------------------------------------------------------------------------
| AARM
| Brain Accuracy Tracker V2
|--------------------------------------------------------------------------
*/

$feedbackFile =
    dirname(__DIR__).'/storage/manual_feedback.json';

$outputFile =
    dirname(__DIR__).'/storage/brain_accuracy.json';

if(!file_exists($feedbackFile)){
    exit('manual_feedback.json NOT FOUND');
}

$feedbacks =
    json_decode(
        file_get_contents($feedbackFile),
        true
    ) ?: [];

$brains = [];

/*
|--------------------------------------------------------------------------
| Rebuild Accuracy
|--------------------------------------------------------------------------
*/

foreach($feedbacks as $row){

    $winnerSide =
        strtoupper(
            $row['winner_side']
            ?? ''
        );

    if(!$winnerSide){
        continue;
    }

    foreach(
        ($row['brains'] ?? [])
        as $brain
    ){

        $brainName =
            strtoupper(
                $brain['brain']
                ?? 'UNKNOWN'
            );

        $vote =
            strtoupper(
                $brain['vote']
                ?? ''
            );

        if(
            !$brainName
            ||
            !$vote
        ){
            continue;
        }

        if(
            !isset(
                $brains[$brainName]
            )
        ){

            $brains[$brainName] = [

                'win' => 0,
                'loss' => 0,
                'total' => 0,
                'wr' => 50,

                'last_update' =>
                    date('Y-m-d H:i:s')

            ];

        }

        if(
            $vote === $winnerSide
        ){

            $brains[$brainName]['win']++;

        }else{

            $brains[$brainName]['loss']++;

        }

        $brains[$brainName]['total'] =

            $brains[$brainName]['win']
            +
            $brains[$brainName]['loss'];

        if(
            $brains[$brainName]['total']
            > 0
        ){

            $brains[$brainName]['wr'] =

                round(

                    (
                        $brains[$brainName]['win']
                        /
                        $brains[$brainName]['total']
                    ) * 100,

                    2

                );

        }

    }

}

/*
|--------------------------------------------------------------------------
| Sort By WR
|--------------------------------------------------------------------------
*/

uasort(

    $brains,

    function($a,$b){

        return
            $b['wr']
            <=>
            $a['wr'];

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
        $brains,
        JSON_PRETTY_PRINT
    )

);

echo PHP_EOL;

echo "==================================\n";
echo " BRAIN ACCURACY TRACKER V2\n";
echo "==================================\n";

echo "Feedback : "
    .count($feedbacks)
    ."\n";

echo "Brains : "
    .count($brains)
    ."\n";

echo "Saved : brain_accuracy.json\n";

echo "==================================\n";