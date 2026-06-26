<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA
| Consensus Explain Engine V2
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);

$consensusFile =
    dirname(__DIR__).'/storage/brain_consensus_v2.json';

$outputFile =
    dirname(__DIR__).'/storage/consensus_explanation_v2.json';

if(!file_exists($consensusFile)){
    exit('brain_consensus_v2.json NOT FOUND');
}

$data =
    json_decode(
        file_get_contents($consensusFile),
        true
    ) ?: [];

$decision =
    $data['decision']
    ?? 'WAIT';

$confidence =
    round(
        $data['confidence']
        ?? 0,
        2
    );

$patternDirection =
    $data['pattern_direction']
    ?? 'UNKNOWN';

$digitDirection =
    $data['digit_direction']
    ?? 'UNKNOWN';

$patternScore =
    round(
        $data['pattern_score']
        ?? 0,
        2
    );

$digitScore =
    round(
        $data['digit_score']
        ?? 0,
        2
    );

$patternKey =
    $data['pattern_key']
    ?? 'UNKNOWN';

$digitKey =
    $data['digit_key']
    ?? 'UNKNOWN';

$reasons = [];

/*
|--------------------------------------------------------------------------
| Decision Reason
|--------------------------------------------------------------------------
*/

$reasons[] =
    'Decision = '.$decision;

$reasons[] =
    'Confidence = '.$confidence.'%';

/*
|--------------------------------------------------------------------------
| Pattern
|--------------------------------------------------------------------------
*/

$reasons[] =
    'Pattern Brain: '
    .$patternKey;

$reasons[] =
    'Pattern Direction: '
    .$patternDirection;

$reasons[] =
    'Pattern Score: '
    .$patternScore;

/*
|--------------------------------------------------------------------------
| Digit
|--------------------------------------------------------------------------
*/

$reasons[] =
    'Digit Brain: '
    .$digitKey;

$reasons[] =
    'Digit Direction: '
    .$digitDirection;

$reasons[] =
    'Digit Score: '
    .$digitScore;

/*
|--------------------------------------------------------------------------
| Consensus Logic
|--------------------------------------------------------------------------
*/

if(
    $patternDirection
    ===
    $digitDirection
){

    $reasons[] =
        'Pattern dan Digit SEARAH';

}else{

    $reasons[] =
        'Pattern dan Digit BERTENTANGAN';

}

if(
    str_contains(
        strtoupper($decision),
        'WAIT'
    )
){

    $reasons[] =
        'WAIT dipilih untuk menghindari konflik sinyal';

}

if(
    str_contains(
        strtoupper($decision),
        'CALL'
    )
){

    $reasons[] =
        'Consensus mendukung CALL';

}

if(
    str_contains(
        strtoupper($decision),
        'PUT'
    )
){

    $reasons[] =
        'Consensus mendukung PUT';

}

/*
|--------------------------------------------------------------------------
| Confidence Label
|--------------------------------------------------------------------------
*/

if($confidence >= 80){

    $confidenceLabel =
        'VERY_HIGH';

}
elseif($confidence >= 70){

    $confidenceLabel =
        'HIGH';

}
elseif($confidence >= 55){

    $confidenceLabel =
        'MEDIUM';

}
else{

    $confidenceLabel =
        'LOW';

}

$reasons[] =
    'Confidence Level: '
    .$confidenceLabel;

/*
|--------------------------------------------------------------------------
| Final Output
|--------------------------------------------------------------------------
*/

$result = [

    'timestamp' =>
        date('Y-m-d H:i:s'),

    'decision' =>
        $decision,

    'confidence' =>
        $confidence,

    'confidence_level' =>
        $confidenceLabel,

    'pattern_key' =>
        $patternKey,

    'pattern_direction' =>
        $patternDirection,

    'pattern_score' =>
        $patternScore,

    'digit_key' =>
        $digitKey,

    'digit_direction' =>
        $digitDirection,

    'digit_score' =>
        $digitScore,

    'reasons' =>
        $reasons

];

file_put_contents(

    $outputFile,

    json_encode(
        $result,
        JSON_PRETTY_PRINT
    )

);

echo PHP_EOL;
echo "==============================".PHP_EOL;
echo "CONSENSUS EXPLAIN ENGINE V2".PHP_EOL;
echo "==============================".PHP_EOL;

echo "Decision   : "
    .$decision.PHP_EOL;

echo "Confidence : "
    .$confidence."%".PHP_EOL;

echo PHP_EOL;

foreach($reasons as $r){

    echo "- ".$r.PHP_EOL;

}

echo PHP_EOL;
echo "Saved : consensus_explanation_v2.json".PHP_EOL;
echo "==============================".PHP_EOL;