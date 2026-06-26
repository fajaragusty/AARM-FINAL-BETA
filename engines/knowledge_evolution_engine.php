<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA ADAPTIVE RESEARCH MACHINE
|--------------------------------------------------------------------------
| KNOWLEDGE EVOLUTION ENGINE RC5
|--------------------------------------------------------------------------
| Purpose
| Knowledge is alive.
| It can learn, survive, decay and reborn.
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

$root = dirname(__DIR__);

$storage = $root.'/storage';

$knowledgeFile = $storage.'/knowledge.json';

if(!file_exists($knowledgeFile)){
    exit("Knowledge not found");
}

$knowledge = json_decode(
    file_get_contents($knowledgeFile),
    true
) ?: [];

foreach($knowledge as &$k){

    $trust = $k['data']['trust'] ?? 50;

    $samples = $k['data']['samples'] ?? 0;

    $wr = $k['data']['wr'] ?? 50;

    $status = $k['status'] ?? 'DISCOVERED';

    /*
    ----------------------------------------------------------
    Life Cycle
    ----------------------------------------------------------
    */

    switch($status){

        case 'DISCOVERED':

            if($trust >= 60){

                $status='LEARNING';

            }

        break;

        case 'LEARNING':

            if($trust>=75 && $samples>=100){

                $status='SURVIVOR';

            }

            elseif($trust<50){

                $status='HIBERNATE';

            }

        break;

        case 'SURVIVOR':

            if($trust>=90 && $samples>=300){

                $status='ELITE';

            }

            elseif($trust<60){

                $status='WATCHLIST';

            }

        break;

        case 'ELITE':

            if($trust<75){

                $status='WATCHLIST';

            }

        break;

        case 'WATCHLIST':

            if($trust>=85){

                $status='SURVIVOR';

            }

            elseif($trust<45){

                $status='DECAY';

            }

        break;

        case 'DECAY':

            if($trust>=70){

                $status='REDISCOVERED';

            }

        break;

        case 'HIBERNATE':

            if($trust>=65){

                $status='REDISCOVERED';

            }

        break;

        case 'REDISCOVERED':

            if($trust>=85){

                $status='SURVIVOR';

            }

        break;

    }

    /*
    ----------------------------------------------------------
    Knowledge Rank
    ----------------------------------------------------------
    */

    $rank = round(

        ($trust*0.45)

        +

        ($wr*0.35)

        +

        (min($samples,500)/500*20)

    ,2);

    /*
    ----------------------------------------------------------
    Mutation Detector
    ----------------------------------------------------------
    */

    $mutation=false;

    if(

        abs(

            $rank

            -

            ($k['rank'] ?? $rank)

        ) > 15

    ){

        $mutation=true;

    }

    /*
    ----------------------------------------------------------
    Evolution History
    ----------------------------------------------------------
    */

    $k['history'][]=[

        'time'=>date('Y-m-d H:i:s'),

        'status'=>$status,

        'rank'=>$rank,

        'trust'=>$trust,

        'wr'=>$wr

    ];

    if(count($k['history'])>200){

        array_shift($k['history']);

    }

    /*
    ----------------------------------------------------------
    Save
    ----------------------------------------------------------
    */

    $k['status']=$status;

    $k['rank']=$rank;

    $k['mutation']=$mutation;

    $k['last_evolution']=date('Y-m-d H:i:s');

}

unset($k);

usort($knowledge,function($a,$b){

    return ($b['rank']??0)<=>

           ($a['rank']??0);

});

file_put_contents(

    $knowledgeFile,

    json_encode(

        array_values($knowledge),

        JSON_PRETTY_PRINT

    )

);

echo "KNOWLEDGE EVOLUTION COMPLETE";