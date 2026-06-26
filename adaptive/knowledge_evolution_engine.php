<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA
| Knowledge Evolution Engine V1
|--------------------------------------------------------------------------
|
| Recalculate status berdasarkan score terbaru
|
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);

$knowledgeFile =
    dirname(__DIR__).'/storage/knowledge_status.json';

$logFile =
    dirname(__DIR__).'/storage/knowledge_evolution_log.json';

if(!file_exists($knowledgeFile)){
    exit('knowledge_status.json NOT FOUND');
}

$knowledge =
    json_decode(
        file_get_contents($knowledgeFile),
        true
    ) ?: [];

$changes = [];

foreach($knowledge as $key => &$item){

    $score =
        floatval(
            $item['score'] ?? 0
        );

    $oldStatus =
        $item['status']
        ?? 'UNKNOWN';

    /*
    |--------------------------------------------------------------------------
    | Evolution Rules V1
    |--------------------------------------------------------------------------
    */

    if($score >= 55){

        $newStatus = 'ACTIVE';

    }elseif($score >= 50){

        $newStatus = 'DECAYING';

    }elseif($score >= 45){

        $newStatus = 'DEAD';

    }else{

        $newStatus = 'GRAVEYARD';

    }

    $item['status'] =
        $newStatus;

    if(
        $oldStatus !== $newStatus
    ){

        $changes[] = [

            'time' =>
                date('Y-m-d H:i:s'),

            'key' =>
                $key,

            'score' =>
                $score,

            'old_status' =>
                $oldStatus,

            'new_status' =>
                $newStatus

        ];

    }

}

unset($item);

file_put_contents(

    $knowledgeFile,

    json_encode(
        $knowledge,
        JSON_PRETTY_PRINT
    )

);

file_put_contents(

    $logFile,

    json_encode(
        $changes,
        JSON_PRETTY_PRINT
    )

);

echo PHP_EOL;
echo "=================================".PHP_EOL;
echo "KNOWLEDGE EVOLUTION ENGINE".PHP_EOL;
echo "=================================".PHP_EOL;
echo "Knowledge : ".count($knowledge).PHP_EOL;
echo "Changed   : ".count($changes).PHP_EOL;
echo "Log File  : knowledge_evolution_log.json".PHP_EOL;
echo "=================================".PHP_EOL;
echo PHP_EOL;