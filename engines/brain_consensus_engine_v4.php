<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA ADAPTIVE RESEARCH MACHINE
|--------------------------------------------------------------------------
| Brain Consensus Engine V4
|--------------------------------------------------------------------------
| FINAL DNA READER
|--------------------------------------------------------------------------
| Brain TIDAK lagi melakukan analisa.
| Brain hanya membaca DNA dan menentukan apakah DNA layak dieksekusi.
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);

$storage = dirname(__DIR__).'/storage/';

$dnaFile = $storage.'dna.json';
$output  = $storage.'brain_consensus_v4.json';

if(!file_exists($dnaFile)){
    exit('dna.json NOT FOUND');
}

$dna = json_decode(
    file_get_contents($dnaFile),
    true
);

if(!is_array($dna)){
    exit('INVALID DNA');
}

/*
|--------------------------------------------------------------------------
| DNA CORE
|--------------------------------------------------------------------------
*/

$fitness =
    (float)($dna['fitness'] ?? 0);

$confidence =
    (float)($dna['confidence'] ?? 0);

$trust =
    (float)($dna['trust'] ?? 0);

$status =
    strtoupper(
        $dna['status'] ?? 'WAIT'
    );

$quality =
    strtoupper(
        $dna['dna_quality'] ?? 'D'
    );

$execution =
    $dna['execution'] ?? [];

$direction =
    strtoupper(
        $execution['direction'] ?? 'WAIT'
    );

$duration =
    (int)(
        $execution['duration'] ?? 3
    );

$expectedWR =
    (float)(
        $execution['expected_wr'] ?? 0
    );

$scorecard =
    $dna['scorecard'] ?? [];

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

$decision = 'WAIT';

$reason = [];

if($status!='READY'){

    $reason[]='DNA_STATUS';

}

if($direction=='WAIT'){

    $reason[]='DNA_WAIT';

}

if($fitness<60){

    $reason[]='LOW_FITNESS';

}

if($confidence<70){

    $reason[]='LOW_CONFIDENCE';

}

if($trust<55){

    $reason[]='LOW_TRUST';

}

if(
    empty($reason)
){

    if($direction=='CALL'){

        $decision='FOLLOW_CALL';

    }

    if($direction=='PUT'){

        $decision='FOLLOW_PUT';

    }

}

/*
|--------------------------------------------------------------------------
| Risk Level
|--------------------------------------------------------------------------
*/

$risk='HIGH';

if(
    $fitness>=80
    &&
    $confidence>=80
    &&
    $trust>=70
){

    $risk='LOW';

}
elseif(
    $fitness>=70
    &&
    $confidence>=75
){

    $risk='MEDIUM';

}

/*
|--------------------------------------------------------------------------
| Brain Score
|--------------------------------------------------------------------------
*/

$brainScore=

round(

(

$fitness*0.40

)

+

(

$confidence*0.40

)

+

(

$trust*0.20

)

,2);

/*
|--------------------------------------------------------------------------
| Consensus
|--------------------------------------------------------------------------
*/

$result=[

'time'=>date('Y-m-d H:i:s'),

'dna_id'=>
$dna['dna_id'] ?? '',

'decision'=>
$decision,

'status'=>
$status,

'direction'=>
$direction,

'duration'=>
$duration,

'expected_wr'=>
$expectedWR,

'fitness'=>
$fitness,

'confidence'=>
$confidence,

'trust'=>
$trust,

'brain_score'=>
$brainScore,

'dna_quality'=>
$quality,

'risk'=>
$risk,

'reason'=>
$reason,

'scorecard'=>
$scorecard,

'execution'=>
$execution

];

file_put_contents(

$output,

json_encode(

$result,

JSON_PRETTY_PRINT

)

);

echo PHP_EOL;

echo "============================".PHP_EOL;

echo " AARM BRAIN V4 ".PHP_EOL;

echo "============================".PHP_EOL;

echo "Decision   : ".$decision.PHP_EOL;

echo "Direction  : ".$direction.PHP_EOL;

echo "Fitness    : ".$fitness.PHP_EOL;

echo "Confidence : ".$confidence.PHP_EOL;

echo "Trust      : ".$trust.PHP_EOL;

echo "Score      : ".$brainScore.PHP_EOL;

echo "Risk       : ".$risk.PHP_EOL;

echo "Status     : ".$status.PHP_EOL;

echo "Saved      : brain_consensus_v4.json".PHP_EOL;

echo "============================".PHP_EOL;