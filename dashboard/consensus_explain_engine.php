<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA
| Consensus Explain Engine V1
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);

$consensusFile =
    dirname(__DIR__).'/storage/brain_consensus_v2.json';

$patternFile =
    dirname(__DIR__).'/storage/knowledge_status.json';

$digitFile =
    dirname(__DIR__).'/storage/digit_scores.json';

$outputFile =
    dirname(__DIR__).'/storage/consensus_explanation.json';

foreach([
    $consensusFile,
    $patternFile,
    $digitFile
] as $file){

    if(!file_exists($file)){
        exit(
            basename($file)
            .' NOT FOUND'
        );
    }

}

$consensus =
    json_decode(
        file_get_contents($consensusFile),
        true
    ) ?: [];

$patterns =
    json_decode(
        file_get_contents($patternFile),
        true
    ) ?: [];

$digits =
    json_decode(
        file_get_contents($digitFile),
        true
    ) ?: [];

/*
|--------------------------------------------------------------------------
| Find Best Pattern
|--------------------------------------------------------------------------
*/

$bestPatternKey = '';
$bestPattern = null;
$bestPatternScore = 0;

foreach($patterns as $key => $row){

    if(
        ($row['status'] ?? '')
        !== 'ACTIVE'
    ){
        continue;
    }

    if(
        ($row['score'] ?? 0)
        > $bestPatternScore
    ){

        $bestPatternScore =
            $row['score'];

        $bestPattern =
            $row;

        $bestPatternKey =
            $key;
    }

}

/*
|--------------------------------------------------------------------------
| Find Best Digit
|--------------------------------------------------------------------------
*/

$bestDigitKey = '';
$bestDigit = null;
$bestDigitWR = 0;

foreach($digits as $key => $row){

    if(
        ($row['wr'] ?? 0)
        > $bestDigitWR
    ){

        $bestDigitWR =
            $row['wr'];

        $bestDigit =
            $row;

        $bestDigitKey =
            $key;
    }

}

/*
|--------------------------------------------------------------------------
| Build Explanation
|--------------------------------------------------------------------------
*/

$decision =
    $consensus['decision']
    ?? 'WAIT';

$confidence =
    $consensus['confidence']
    ?? 0;

$reasons = [];

if($bestPattern){

    $reasons[] =

        'Pattern: '
        .$bestPatternKey

        .' | Score: '
        .$bestPattern['score']

        .' | Status: '
        .$bestPattern['status'];

}

if($bestDigit){

    $reasons[] =

        'Digit: '
        .$bestDigitKey

        .' | WR: '
        .$bestDigit['wr']

        .'%';

}

if(
    $decision
    !== 'WAIT'
){

    $reasons[] =

        'Consensus agreement detected';

}
else{

    $reasons[] =

        'Brains disagree → WAIT';

}

if($confidence >= 70){

    $reasons[] =
        'High confidence';

}
elseif($confidence >= 55){

    $reasons[] =
        'Medium confidence';

}
else{

    $reasons[] =
        'Low confidence';

}

/*
|--------------------------------------------------------------------------
| Final
|--------------------------------------------------------------------------
*/

$result = [

    'timestamp' =>
        date('Y-m-d H:i:s'),

    'decision' =>
        $decision,

    'confidence' =>
        $confidence,

    'reasons' =>
        $reasons,

    'pattern_key' =>
        $bestPatternKey,

    'digit_key' =>
        $bestDigitKey

];

file_put_contents(

    $outputFile,

    json_encode(
        $result,
        JSON_PRETTY_PRINT
    )

);

echo PHP_EOL;
echo "========================".PHP_EOL;
echo "CONSENSUS EXPLAIN".PHP_EOL;
echo "========================".PHP_EOL;

echo "Decision   : "
    .$decision.PHP_EOL;

echo "Confidence : "
    .$confidence.'%'.PHP_EOL;

echo PHP_EOL;

foreach($reasons as $r){

    echo '- '.$r.PHP_EOL;

}

echo PHP_EOL;
echo "Saved : consensus_explanation.json".PHP_EOL;
echo "========================".PHP_EOL;