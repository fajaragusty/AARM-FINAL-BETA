<?php

$experienceFile =
    dirname(__DIR__).'/storage/experience.json';

$adaptiveFile =
    dirname(__DIR__).'/storage/adaptive.json';

$experience = json_decode(
    file_get_contents($experienceFile),
    true
) ?: [];

if(
    !is_array($experience)
    ||
    count($experience) === 0
){
    exit('EXPERIENCE NOT READY');
}

$bestPattern = null;
$bestTrust = 0;

foreach($experience as $pattern=>$row){

    $trust =
        $row['trust'] ?? 0;

    $total =
        $row['total'] ?? 0;

    /*
    |--------------------------------------------------
    | Minimal Sample Filter
    |--------------------------------------------------
    */

    if($total < 3){
        continue;
    }

    if($trust > $bestTrust){

        $bestTrust =
            $trust;

        $bestPattern =
            $pattern;

    }

}

$mode = 'WAIT';

if($bestTrust >= 55){

    $mode = 'FOLLOW';

}

if($bestTrust < 50){

    $mode = 'DEFENSIVE';

}

$data = [

    'updated_at' =>
        date('Y-m-d H:i:s'),

    'mode' =>
        $mode,

    'confidence' =>
        round(
            $bestTrust,
            2
        ),

    'active_pattern' =>
        $bestPattern,

    'source' =>
        'LIVE_EXPERIENCE'

];

file_put_contents(

    $adaptiveFile,

    json_encode(
        $data,
        JSON_PRETTY_PRINT
    )

);

echo
    "ADAPTIVE LIVE UPDATED : "
    .$bestPattern
    ." ("
    .$bestTrust
    ."%)";