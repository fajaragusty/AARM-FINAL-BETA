<?php

$history = json_decode(
    file_get_contents(
        dirname(__DIR__).'/storage/historical_ticks.json'
    ),
    true
) ?: [];

if(count($history) < 100){
    exit('NOT ENOUGH DATA');
}

$knowledge = [];

for($i=0; $i<count($history)-5; $i++){

    $price =
        $history[$i]['quote'];

    $priceString =
        (string)$price;

    $digit =
        substr(
            preg_replace('/[^0-9]/','',$priceString),
            -1
        );

    if($digit === ''){
        continue;
    }

    foreach(['CALL','PUT'] as $direction){

        for($duration=1; $duration<=5; $duration++){

            if(!isset($history[$i+$duration])){
                continue;
            }

            $entry =
                $history[$i]['quote'];

            $exit =
                $history[$i+$duration]['quote'];

            $key =
                'DIGIT_'
                .$digit
                .'|'
                .$direction
                .'|'
                .$duration;

            if(!isset($knowledge[$key])){

                $knowledge[$key] = [

                    'digit' =>
                        $digit,

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
                $result='WIN';
            }

            if(
                $direction === 'PUT'
                &&
                $exit < $entry
            ){
                $result='WIN';
            }

            $knowledge[$key]['samples']++;

            if($result==='WIN'){
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
            ($row['win']/$total)*100,
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

    dirname(__DIR__).'/storage/digit_knowledge.json',

    json_encode(
        $knowledge,
        JSON_PRETTY_PRINT

    )

);

echo 'DIGIT KNOWLEDGE UPDATED';