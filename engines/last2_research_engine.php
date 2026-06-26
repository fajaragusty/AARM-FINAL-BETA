<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA
| Last2 Research Engine V1
|--------------------------------------------------------------------------
*/

$historyFile =
    dirname(__DIR__).'/storage/historical_ticks.json';

$outputFile =
    dirname(__DIR__).'/storage/last2_knowledge.json';

if(!file_exists($historyFile)){
    exit('historical_ticks.json NOT FOUND');
}

$ticks = json_decode(
    file_get_contents($historyFile),
    true
) ?: [];

if(count($ticks) < 100){
    exit('NOT ENOUGH DATA');
}

$knowledge = [];

for($i=0; $i<count($ticks)-5; $i++){

    $price =
        $ticks[$i]['quote'];

    $digits =
        preg_replace(
            '/[^0-9]/',
            '',
            (string)$price
        );

    $last2 =
        substr(
            $digits,
            -2
        );

    if(strlen($last2) != 2){
        continue;
    }

    foreach(['CALL','PUT'] as $direction){

        for($duration=1; $duration<=5; $duration++){

            if(!isset($ticks[$i+$duration])){
                continue;
            }

            $entry =
                $ticks[$i]['quote'];

            $exit =
                $ticks[$i+$duration]['quote'];

            $key =
                'LAST2_'
                .$last2
                .'|'
                .$direction
                .'|'
                .$duration;

            if(!isset($knowledge[$key])){

                $knowledge[$key] = [

                    'last2' =>
                        $last2,

                    'direction' =>
                        $direction,

                    'duration' =>
                        $duration,

                    'win' => 0,

                    'loss' => 0,

                    'samples' => 0

                ];

            }

            $result = 'LOSS';

            if(
                $direction === 'CALL'
                &&
                $exit > $entry
            ){
                $result = 'WIN';
            }

            if(
                $direction === 'PUT'
                &&
                $exit < $entry
            ){
                $result = 'WIN';
            }

            $knowledge[$key]['samples']++;

            if($result === 'WIN'){

                $knowledge[$key]['win']++;

            }else{

                $knowledge[$key]['loss']++;

            }

        }

    }

}

foreach($knowledge as &$row){

    $total =
        $row['win']
        +
        $row['loss'];

    $row['wr'] =
        $total
        ?
        round(
            ($row['win'] / $total) * 100,
            2
        )
        :
        0;

}

uasort(

    $knowledge,

    function($a,$b){

        return
            $b['wr']
            <=>
            $a['wr'];

    }

);

file_put_contents(

    $outputFile,

    json_encode(
        $knowledge,
        JSON_PRETTY_PRINT
    )

);

echo 'LAST2 KNOWLEDGE UPDATED';