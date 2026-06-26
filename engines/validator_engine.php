<?php
/*
|--------------------------------------------------------------------------
| AARM RC-FINAL
| PHASE 1
| PART 1
| VALIDATOR ENGINE
|--------------------------------------------------------------------------
| PURPOSE
| Read-only validator.
| NEVER modifies engine output.
| NEVER generates signals.
| ONLY observes pipeline health.
|--------------------------------------------------------------------------
*/

date_default_timezone_set('Asia/Jakarta');

define('ROOT', dirname(__DIR__));
define('STORAGE', ROOT . '/storage/');

header('Content-Type: application/json');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function loadJson(string $file): array
{
    $path = STORAGE . $file;
    if (!file_exists($path)) {
        return [];
    }
    $json = json_decode(file_get_contents($path), true);
    return is_array($json) ? $json : [];
}

function fileAge(string $file): ?int
{
    $path = STORAGE . $file;
    if (!file_exists($path)) {
        return null;
    }
    return time() - filemtime($path);
}

function engineStatus(?int $age): string
{
    if ($age === null) {
        return 'OFFLINE';
    }
    if ($age <= 3) {
        return 'ONLINE';
    }
    if ($age <= 10) {
        return 'DELAY';
    }
    return 'OFFLINE';
}

/*
|--------------------------------------------------------------------------
| Collector
|--------------------------------------------------------------------------
*/

$ticks = loadJson('historical_ticks.json');
$totalTicks = count($ticks);
$lastTick = $ticks[$totalTicks - 1] ?? [];

// Perbaikan: Logika delay dipindah ke atas sebelum inisialisasi array
$lastEpoch = (int)($lastTick['epoch'] ?? 0);
$collectorDelay = $lastEpoch > 0 
    ? time() - $lastEpoch 
    : fileAge('historical_ticks.json');

$collector = [
    'alive' => file_exists(STORAGE . 'historical_ticks.json'),
    'status' => engineStatus($collectorDelay),
    'delay' => $collectorDelay,
    'tick_count' => $totalTicks,
    'last_tick_time' => $lastTick['epoch'] ?? null,
    'last_price' => $lastTick['quote'] ?? null
];

/*
|--------------------------------------------------------------------------
| Research
|--------------------------------------------------------------------------
*/

$researchFiles = [
    'trend_research.json',
    'pattern_research.json',
    'digit_research.json',
    'indicator_research.json',
    'hybrid_research.json'
];

$research = [
    'alive' => false,
    'status' => 'OFFLINE',
    'updated' => null,
    'engines' => []
];

$researchAlive = 0;
$latestResearchAge = null;

foreach ($researchFiles as $file) {
    $data = loadJson($file);
    $age = fileAge($file);
    $status = engineStatus($age);

    if ($status !== 'OFFLINE') {
        $researchAlive++;
    }

    if ($age !== null) {
        if ($latestResearchAge === null || $age < $latestResearchAge) {
            $latestResearchAge = $age;
        }
    }

    $research['engines'][] = [
        'file' => $file,
        'status' => $status,
        'delay' => $age,
        'records' => is_array($data) ? count($data) : 0
    ];
}

$research['alive'] = ($researchAlive > 0);
$research['status'] = engineStatus($latestResearchAge);
$research['updated'] = $latestResearchAge;

/*
|--------------------------------------------------------------------------
| Knowledge
|--------------------------------------------------------------------------
*/

$knowledgeBest = loadJson('knowledge_best.json');
$knowledgeOpt = loadJson('knowledge_optimized.json');

$knowledge = [
    'alive' => file_exists(STORAGE . 'knowledge_best.json'),
    'status' => engineStatus(fileAge('knowledge_best.json')),
    'delay' => fileAge('knowledge_best.json'),
    'records' => count($knowledgeBest),
    'optimizer_records' => count($knowledgeOpt)
];

/*
|--------------------------------------------------------------------------
| Brain
|--------------------------------------------------------------------------
*/

// Perbaikan: Mengubah nama data mentah agar tidak tumpang tindih dengan array output
$brainRaw = loadJson('brain_consensus_v4.json');
$brainAge = fileAge('brain_consensus_v4.json');

$brain = [
    'alive'       => file_exists(STORAGE . 'brain_consensus_v4.json'),
    'status'      => engineStatus($brainAge),
    'delay'       => $brainAge,
    'decision'    => strtoupper($brainRaw['decision'] ?? 'WAIT'),
    'confidence'  => (float)($brainRaw['confidence'] ?? 0),
    'fitness'     => (float)($brainRaw['fitness'] ?? 0),
    'risk'        => strtoupper($brainRaw['risk'] ?? 'UNKNOWN'),
    'consensus'   => $brainRaw['consensus'] ?? '-',
    'dna'         => $brainRaw['dna'] ?? '-',
    'reason_count'=> is_array($brainRaw['reason'] ?? null) ? count($brainRaw['reason']) : 1
];

/*
|--------------------------------------------------------------------------
| DNA
|--------------------------------------------------------------------------
*/

$dnaData = loadJson('dna.json');
$dnaAge = fileAge('dna.json');

$dna = [
    'alive'  => file_exists(STORAGE.'dna.json'),
    'status' => engineStatus($dnaAge),
    'delay'  => $dnaAge,
    'records'=> count($dnaData)
];

/*
|--------------------------------------------------------------------------
| Decision Quality
|--------------------------------------------------------------------------
*/

$decisionQuality = "LOW";
if ($brain['confidence'] >= 80 && $brain['fitness'] >= 80) {
    $decisionQuality = "HIGH";
} elseif ($brain['confidence'] >= 60) {
    $decisionQuality = "MEDIUM";
}

$brain['quality'] = $decisionQuality;

/*
|--------------------------------------------------------------------------
| Update & Build Validator Object Utuh
|--------------------------------------------------------------------------
*/

// Perbaikan: Deklarasi tunggal untuk menggabungkan seluruh komponen tanpa ditimpa kosong
$validator = [
    'generated_at' => time(),
    'collector'    => $collector,
    'research'     => $research,
    'knowledge'    => $knowledge,
    'brain'        => $brain,
    'dna'          => $dna
];

/*
|--------------------------------------------------------------------------
| Save to File
|--------------------------------------------------------------------------
*/

file_put_contents(
    STORAGE . 'validator.json',
    json_encode($validator, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

/*
|--------------------------------------------------------------------------
| Output JSON
|--------------------------------------------------------------------------
*/

echo json_encode($validator, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);