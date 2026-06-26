<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA
| Brain Consensus Engine V3
|--------------------------------------------------------------------------
| V2 + Traceability
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);

/*
|--------------------------------------------------------------------------
| RC2 Generic Brain Processor
|--------------------------------------------------------------------------
*/
function processBrain($name, $vote, $score, $key, $statusData, $brainWeights, $statusWeight) {
    $status = $statusData[$key]['status'] ?? 'UNPROVEN';
    $statusWeightValue = $statusWeight[$status] ?? 0.60;
    $brainWeight = $brainWeights[$name]['weight'] ?? 1.00;
    $weight = round($statusWeightValue * $brainWeight, 2);
    $final = round($score * $weight, 2);
    return [
        'brain'  => $name,
        'key'    => $key,
        'vote'   => $vote,
        'score'  => $score,
        'status' => $status,
        'weight' => $weight,
        'final'  => $final,
        'reason' => $name . ' knowledge selected'
    ];
}

// File paths
$brainFile      = dirname(__DIR__) . '/storage/brain_state.json';
$statusFile     = dirname(__DIR__) . '/storage/knowledge_status.json';
$experienceFile = dirname(__DIR__) . '/storage/experience.json';
$trustFile      = dirname(__DIR__) . '/storage/trust.json';
$weightFile     = dirname(__DIR__) . '/storage/brain_weights.json';
$outputFile     = dirname(__DIR__) . '/storage/brain_consensus_v3.json';

// Validation
foreach ([$brainFile, $statusFile] as $file) {
    if (!file_exists($file)) {
        exit(basename($file) . ' NOT FOUND' . PHP_EOL);
    }
}

// Load Data
$brain = json_decode(file_get_contents($brainFile), true) ?: [];
$statusData = json_decode(file_get_contents($statusFile), true) ?: [];

$experienceData = [];
if (file_exists($experienceFile)) {
    $experienceData = json_decode(file_get_contents($experienceFile), true) ?: [];
}

$trustData = [];
if (file_exists($trustFile)) {
    $trustData = json_decode(file_get_contents($trustFile), true) ?: [];
}

$brainWeights = [];
if (file_exists($weightFile)) {
    $brainWeights = json_decode(file_get_contents($weightFile), true) ?: [];
}

/*
|--------------------------------------------------------------------------
| Status Weight Mapping
|--------------------------------------------------------------------------
*/
$statusWeight = [
    'ACTIVE'     => 1.00,
    'UNPROVEN'   => 0.60,
    'DECAYING'   => 0.40,
    'DEAD'       => 0.20,
    'GRAVEYARD'  => 0.00
];

$callWeight = 0;
$putWeight  = 0;
$brains     = [];

/*
|--------------------------------------------------------------------------
| Pattern Brain
|--------------------------------------------------------------------------
*/
$patternVote  = $brain['pattern_vote'] ?? 'WAIT';
$patternScore = $brain['pattern_score'] ?? 0;
$patternKey   = $brain['pattern_key'] ?? '';

// Memanfaatkan fungsi processBrain agar kode rapi
$patternData = processBrain('PATTERN', $patternVote, $patternScore, $patternKey, $statusData, $brainWeights, $statusWeight);

$expBonus   = $experienceData[$patternKey]['research_score'] ?? 0;
$trustBonus = $trustData[$patternKey]['trust'] ?? 50;

// Kalkulasi Final Score Pattern + Bonus
$patternFinal = round($patternData['final'] + ($expBonus * 0.15) + ($trustBonus * 0.10), 2);
$patternData['final'] = $patternFinal; // Update nilai final di array

if ($patternVote === 'CALL') {
    $callWeight += $patternFinal;
} elseif ($patternVote === 'PUT') {
    $putWeight += $patternFinal;
}
$brains[] = $patternData;

/*
|--------------------------------------------------------------------------
| Digit Brain
|--------------------------------------------------------------------------
*/
$digitVote  = $brain['digit_vote'] ?? 'WAIT';
$digitScore = $brain['digit_score'] ?? 0;
$digitKey   = $brain['digit_key'] ?? '';

$digitData = processBrain(
    'DIGIT',
    $digitVote,
    $digitScore,
    $digitKey,
    $statusData,
    $brainWeights,
    $statusWeight
);

$digitExpBonus =
    $experienceData[$digitKey]['research_score'] ?? 0;

$digitTrustBonus =
    $trustData[$digitKey]['trust'] ?? 50;

$digitFinal = round(

    $digitData['final']

    +

    ($digitExpBonus * 0.15)

    +

    ($digitTrustBonus * 0.10)

,2);

$digitData['final']=$digitFinal;

if ($digitVote === 'CALL') {
    $callWeight += $digitFinal;
} elseif ($digitVote === 'PUT') {
    $putWeight += $digitFinal;
}
$brains[] = $digitData;

/*
|--------------------------------------------------------------------------
| Metrics Calculation (Dipindahkan ke atas sebelum Decision Logic)
|--------------------------------------------------------------------------
*/
$total = $callWeight + $putWeight;
$confidence = 0;

if ($total > 0) {
    $confidence = round((max($callWeight, $putWeight) / $total) * 100, 2);
}

$delta = abs($callWeight - $putWeight);

/*
|--------------------------------------------------------------------------
| Decision Logic
|--------------------------------------------------------------------------
*/
$avgTrust = 0;

if(count($brains) > 0){

    $sumTrust = 0;

    foreach($brains as $b){

        $sumTrust += $trustData[$b['key']]['trust'] ?? 50;

    }

    $avgTrust = round($sumTrust / count($brains),2);

}

$decision = 'WAIT';

if(
    $confidence >= 70
    &&
    $delta >= 8
    &&
    $avgTrust >= 65
){
	
    $decision = ($callWeight > $putWeight) ? 'FOLLOW_CALL' : 'FOLLOW_PUT';
} elseif ($confidence >= 60) {
    $decision = 'WAIT_CONFIRMATION';
} else {
    $decision = 'WAIT';
}

/*
|--------------------------------------------------------------------------
| Consensus Reason
|--------------------------------------------------------------------------
*/
if ($patternVote === $digitVote && $patternVote !== 'WAIT') {
    $consensusReason = 'AGREEMENT';
} elseif ($decision === 'WAIT' || $decision === 'WAIT_CONFIRMATION') {
    $consensusReason = 'CONFLICT_WAIT';
} else {
    $consensusReason = 'WEIGHTED_WIN';
}

$winnerSide = ($callWeight > $putWeight) ? 'CALL' : 'PUT';

/*
|--------------------------------------------------------------------------
| Save Result
|--------------------------------------------------------------------------
*/
$result = [
    'time'             => date('Y-m-d H:i:s'),
    'decision'         => $decision,
    'confidence'       => $confidence,
    'confidence_level' => ($confidence >= 80) ? 'VERY_HIGH' : (($confidence >= 70) ? 'HIGH' : (($confidence >= 55) ? 'MEDIUM' : 'LOW')),
    'call_weight'      => round($callWeight, 2),
    'put_weight'       => round($putWeight, 2),
    'delta'            => round($delta, 2),
    'winner_side'      => $winnerSide,
    'consensus_reason' => $consensusReason,
    'brains'           => $brains
];

file_put_contents($outputFile, json_encode($result, JSON_PRETTY_PRINT));

// Output CLI
echo PHP_EOL;
echo "==============================" . PHP_EOL;
echo "BRAIN CONSENSUS V3" . PHP_EOL;
echo "==============================" . PHP_EOL;
echo "Decision   : " . $decision . PHP_EOL;
echo "Confidence : " . $confidence . "%" . PHP_EOL;
echo "Delta      : " . $delta . PHP_EOL;
echo "Reason     : " . $consensusReason . PHP_EOL;
echo "Saved      : brain_consensus_v3.json" . PHP_EOL;
echo "==============================" . PHP_EOL;