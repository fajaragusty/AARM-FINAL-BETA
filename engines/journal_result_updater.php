<?php

error_reporting(E_ALL);

$journalFile =
    dirname(__DIR__).'/storage/trade_journal.json';

if(!file_exists($journalFile)){
    exit('trade_journal.json NOT FOUND');
}

$tradeId =
    $_GET['id'] ?? '';

$result =
    strtoupper(
        $_GET['result'] ?? ''
    );

if(
    empty($tradeId)
){
    exit('Missing id');
}

if(
    !in_array(
        $result,
        ['WIN','LOSS']
    )
){
    exit('Result must be WIN or LOSS');
}

$journal =
    json_decode(
        file_get_contents($journalFile),
        true
    ) ?: [];

$found = false;

foreach($journal as &$trade){

    if(
        ($trade['id'] ?? '')
        !==
        $tradeId
    ){
        continue;
    }

    $trade['result'] = $result;

    $trade['updated_at'] =
        date('Y-m-d H:i:s');

    $found = true;

    break;
}

unset($trade);

if(!$found){
    exit('Trade ID NOT FOUND');
}

file_put_contents(
    $journalFile,
    json_encode(
        $journal,
        JSON_PRETTY_PRINT
    )
);

echo "SUCCESS : {$tradeId} => {$result}";