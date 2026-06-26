<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA ADAPTIVE RESEARCH MACHINE
|--------------------------------------------------------------------------
| DNA ENGINE
|--------------------------------------------------------------------------
| Version : FINAL RC - FIXED & OPTIMIZED
|--------------------------------------------------------------------------
| DNA adalah SATU-SATUNYA object yang dibaca Brain
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);
date_default_timezone_set('Asia/Jakarta');

class DNAEngine
{
    private string $storage;
    private array $knowledge = [];
    private array $context = [];
    private array $fusion = [];
    private array $digit = [];
    private array $last2 = [];
    private array $trust = [];
    private array $scores = [];
    private array $experience = [];
    private array $dna = [];

    public function __construct()
    {
        $this->storage = dirname(__DIR__) . "/storage";
    }

    /*
    |--------------------------------------------------------------------------
    | JSON Loader
    |--------------------------------------------------------------------------
    */
    private function load(string $file): array
    {
        $path = $this->storage . "/" . $file;
        if (!file_exists($path)) {
            return [];
        }
        $json = file_get_contents($path);
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    /*
    |--------------------------------------------------------------------------
    | Load Knowledge Layer
    |--------------------------------------------------------------------------
    */
    public function loadKnowledge(): void
    {
        $this->knowledge  = $this->load("knowledge_best.json");
        $this->context    = $this->load("context_best.json");
        $this->fusion     = $this->load("fusion_best.json");
        $this->digit      = $this->load("digit_knowledge.json");
        $this->last2      = $this->load("last2_knowledge.json");
        $this->trust      = $this->load("trust.json");
        $this->scores     = $this->load("knowledge_scores.json");
        $this->experience = $this->load("experience.json");
    }

    /*
    |--------------------------------------------------------------------------
    | DNA Skeleton
    |--------------------------------------------------------------------------
    */
    private function newDNA(): array
    {
        return [
            "dna_id"      => "",
            "created_at"  => date("Y-m-d H:i:s"),
            "fitness"     => 0,
            "confidence"  => 0,
            "trust"       => 0,
            "life_stage"  => "LEARNING",
            "generation"  => 1,
            "samples"     => 0,
            "genes"       => [
                "pattern"    => null,
                "context"    => null,
                "digit"      => null,
                "last2"      => null,
                "fusion"     => null,
                "experience" => null
            ],
            "execution"   => [
                "direction"   => "WAIT",
                "duration"    => 3,
                "expected_wr" => 0
            ],
            "history"     => []
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Initialize DNA
    |--------------------------------------------------------------------------
    */
    public function initialize(): void
    {
        $this->loadKnowledge();
        $this->dna = $this->newDNA();
    }

    /*
    |--------------------------------------------------------------------------
    | Find Best Record (Sorting Strategy)
    |--------------------------------------------------------------------------
    */
    private function best(array $groups): ?array
    {
        if (empty($groups)) {
            return null;
        }

        $winner = null;
        foreach ($groups as $group) {
            if (isset($group['wr'])) {
                $candidate = $group;
            } else {
                if (!is_array($group) || empty($group)) {
                    continue;
                }
                $candidate = $group[0] ?? null;
            }

            if (!$candidate) {
                continue;
            }

            if ($winner === null) {
                $winner = $candidate;
                continue;
            }

            $scoreA = ($candidate['optimizer_score'] ?? 0) +
                      (($candidate['wr'] ?? 0) * 0.20) +
                      min(20, ($candidate['samples'] ?? 0) / 20);

            $scoreB = ($winner['optimizer_score'] ?? 0) +
                      (($winner['wr'] ?? 0) * 0.20) +
                      min(20, ($winner['samples'] ?? 0) / 20);

            if ($scoreA > $scoreB) {
                $winner = $candidate;
            }
        }
        return $winner;
    }

    /*
    |--------------------------------------------------------------------------
    | Gene Builders
    |--------------------------------------------------------------------------
    */
    private function buildContextGene(): void { $this->dna['genes']['context'] = $this->best($this->context); }
    
    private function buildPatternGene(): void
    {
        $gene = $this->best($this->knowledge);

        if ($gene !== null) {
            $this->dna['genes']['pattern'] = $gene;
            return;
        }

        if (!empty($this->experience)) {
            $best = null;

            foreach ($this->experience as $row) {
                if (!is_array($row)) {
                    continue;
                }

                if ($best === null) {
                    $best = $row;
                    continue;
                }

                $wrA = (float)($row['trust'] ?? 0);
                $wrB = (float)($best['trust'] ?? 0);

                if ($wrA > $wrB) {
                    $best = $row;
                }
            }

            if ($best !== null) {
                $this->dna['genes']['pattern'] = [
                    'pattern'   => $best['pattern'] ?? '',
                    'direction' => $best['direction'] ?? 'WAIT',
                    'duration'  => $best['duration'] ?? 3,
                    'win'       => $best['win'] ?? 0,
                    'loss'      => $best['loss'] ?? 0,
                    'samples'   => $best['total'] ?? 0,
                    'wr'        => $best['trust'] ?? 0
                ];
            }
        }
    }

    private function buildDigitGene(): void   { $this->dna['genes']['digit'] = $this->best($this->digit); }
    private function buildLast2Gene(): void   { $this->dna['genes']['last2'] = $this->best($this->last2); }
    private function buildFusionGene(): void  { $this->dna['genes']['fusion'] = $this->best($this->fusion); }
    
    private function buildExperienceGene(): void
    {
        if (!empty($this->experience)) {
            $this->dna['genes']['experience'] = $this->experience;
        }
    }

    private function assembleGenes(): void
    {
        $this->buildPatternGene();
        $this->buildContextGene();
        $this->buildDigitGene();
        $this->buildLast2Gene();
        $this->buildFusionGene();
        $this->buildExperienceGene();
    }

    /*
    |--------------------------------------------------------------------------
    | PATCH RC5.1 - Integrated & Cleaned
    |--------------------------------------------------------------------------
    */
    private function calculateFitness(): void
    {
        $fitness = 0;
        $confidence = 0;
        $trust = 0;
        $totalSamples = 0;
        $geneCount = 0;

        foreach ($this->dna['genes'] as $name => $gene) {
            if (!is_array($gene) || empty($gene)) {
                continue;
            }
            if ($name === "experience") {
                continue;
            }

            $wr      = (float)($gene['wr'] ?? 0);
            $score   = (float)($gene['score'] ?? $gene['fusion_score'] ?? $wr);
            $samples = (int)($gene['samples'] ?? 1);

            // Sample Weight Calculation (250 sample = 100%)
            $sampleWeight = min(100, ($samples / 250) * 100);
            $trustWeight  = 50;

            if (!empty($this->trust)) {
                $trustWeight = (float)($this->trust['trust'] ?? $this->trust['score'] ?? 50);
            }

            $geneFitness    = round(($wr * 0.60) + ($sampleWeight * 0.20) + ($trustWeight * 0.20), 2);
            $geneConfidence = round(($wr * 0.70) + ($trustWeight * 0.30), 2);
            
            // Fusion Agreement Bonus
            if ($name === "fusion" && (($gene['status'] ?? '') === 'AGREEMENT')) {
                $geneFitness += 5;
                $geneConfidence += 5;
            }

            // Trust Bonus based on Win Rate
            if ($wr >= 80) {
                $geneFitness += 8;
            } elseif ($wr >= 70) {
                $geneFitness += 5;
            } elseif ($wr >= 60) {
                $geneFitness += 2;
            }

            $fitness      += max(0, $geneFitness);
            $confidence   += max(0, $geneConfidence);
            $totalSamples += $samples;
            $geneCount++;
        }

        // Calculate Averages
        if ($geneCount > 0) {
            $fitness    /= $geneCount;
            $confidence /= $geneCount;
        }

        // Trust Engine Integration
        if (!empty($this->trust)) {
            $trust = $this->trust['trust'] ?? $this->trust['score'] ?? $this->trust['overall'] ?? 0;
        }

        // Experience Fallback
        if ($trust <= 0) {
            $pattern = $this->dna['genes']['pattern']['pattern'] ?? null;
            if ($pattern && isset($this->experience[$pattern]['trust'])) {
                $trust = (float)($this->experience[$pattern]['trust'] ?? 50);
            }
        }

        // Global Scores Bonus Integration
        if (!empty($this->scores)) {
            if (isset($this->scores['fitness'])) {
                $fitness = ($fitness * 0.80) + ($this->scores['fitness'] * 0.20);
            }
            if (isset($this->scores['confidence'])) {
                $confidence = ($confidence * 0.80) + ($this->scores['confidence'] * 0.20);
            }
        }

        // Normalization (Bounds between 0 and 100)
        $fitness    = round(max(0, min(100, $fitness)), 2);
        $confidence = round(max(0, min(100, $confidence)), 2);
        $trust      = round(max(0, min(100, $trust)), 2);

        // Grade Assignment
        $qualityScore = ($fitness * 0.40) + ($confidence * 0.40) + ($trust * 0.20);

        if ($qualityScore >= 95) {
            $quality = "A+";
        } elseif ($qualityScore >= 90) {
            $quality = "A";
        } elseif ($qualityScore >= 80) {
            $quality = "B";
        } elseif ($qualityScore >= 70) {
            $quality = "C";
        } else {
            $quality = "D";
        }

        $this->dna['fitness']     = $fitness;
        $this->dna['confidence']  = $confidence;
        $this->dna['trust']       = $trust;
        $this->dna['samples']     = $totalSamples;
        $this->dna['dna_quality'] = $quality;
    }

    /*
    |--------------------------------------------------------------------------
    | Determine Life Stage
    |--------------------------------------------------------------------------
    */
    private function determineLifeStage(): void
    {
        $fitness = $this->dna['fitness'];
        if ($fitness >= 95) {
            $this->dna['life_stage'] = 'MASTER';
        } elseif ($fitness >= 85) {
            $this->dna['life_stage'] = 'ELITE';
        } elseif ($fitness >= 70) {
            $this->dna['life_stage'] = 'ADVANCED';
        } elseif ($fitness >= 50) {
            $this->dna['life_stage'] = 'LEARNING';
        } else {
            $this->dna['life_stage'] = 'NEW';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PATCH RC5.2 - PART 2: Execution Builder
    |--------------------------------------------------------------------------
    */
    private function buildExecution(): void
    {
        $pattern = $this->dna['genes']['pattern'] ?? [];
        $context = $this->dna['genes']['context'] ?? [];
        $digit   = $this->dna['genes']['digit'] ?? [];
        $last2   = $this->dna['genes']['last2'] ?? [];
        $fusion  = $this->dna['genes']['fusion'] ?? [];

        $votes = [];
        foreach ([$pattern, $context, $digit, $last2] as $gene) {
            if (!empty($gene['direction'])) {
                $votes[] = strtoupper($gene['direction']);
            }
        }

        $callVotes = 0;
        $putVotes  = 0;
        foreach ($votes as $vote) {
            if ($vote === 'CALL') $callVotes++;
            if ($vote === 'PUT')  $putVotes++;
        }

        $direction = "WAIT";
        if ($callVotes > $putVotes) {
            $direction = "CALL";
        } elseif ($putVotes > $callVotes) {
            $direction = "PUT";
        }

        // Fusion Agreement Override
        if (isset($fusion['status']) && $fusion['status'] === 'AGREEMENT') {
            $direction = $fusion['pattern_direction'] ?? $direction;
        }

        // Context Filter & Protection
        $market = strtoupper($context['market_state'] ?? '');
        $trend  = strtoupper($context['trend'] ?? '');
        $rsi    = strtoupper($context['rsi_zone'] ?? '');

        if ($market === 'RANGING' && $this->dna['confidence'] < 70) {
            $direction = "WAIT";
        }
        if ($direction === 'CALL' && $trend === 'BEARISH') {
            $direction = "WAIT";
        }
        if ($direction === 'PUT' && $trend === 'BULLISH' && $this->dna['confidence'] < 75) {
            $direction = "WAIT";
        }
        if ($direction === 'CALL' && $rsi === 'RSI_HIGH') {
            $direction = "WAIT";
        }
        if ($direction === 'PUT' && $rsi === 'RSI_LOW') {
            $direction = "WAIT";
        }

        $status = ($direction === "WAIT") ? "WAIT" : "READY";

        $this->dna['scorecard'] = [
            "pattern"   => round($pattern['wr'] ?? 0, 2),
            "context"   => round($context['wr'] ?? 0, 2),
            "digit"     => round($digit['wr'] ?? 0, 2),
            "last2"     => round($last2['wr'] ?? 0, 2),
            "fusion"    => round($fusion['fusion_score'] ?? 0, 2),
            "call_vote" => $callVotes,
            "put_vote"  => $putVotes
        ];
        $this->dna['status'] = $status;
        
        /*
        |--------------------------------------------------------------------------
        | Consensus Bonus
        |--------------------------------------------------------------------------
        */
        $totalVotes = $callVotes + $putVotes;
        if ($totalVotes > 0) {
            $voteStrength = max($callVotes, $putVotes) / $totalVotes;
            $bonus = 0;

            if ($voteStrength >= 1.00) {
                $bonus = 12;
            } elseif ($voteStrength >= 0.75) {
                $bonus = 8;
            } elseif ($voteStrength >= 0.60) {
                $bonus = 4;
            }

            $this->dna['confidence'] = min(100, round($this->dna['confidence'] + $bonus, 2));
        }

        $durationVotes = [];
        foreach ([$pattern, $context, $digit, $last2] as $gene) {
            if (!empty($gene['duration'])) {
                $d = (int)$gene['duration'];
                $durationVotes[$d] = ($durationVotes[$d] ?? 0) + 1;
            }
        }

        arsort($durationVotes);
        $duration = (int)array_key_first($durationVotes);

        $this->dna['execution'] = [
            "direction"   => $direction,
            "duration"    => $duration,
            "expected_wr" => round($this->dna['confidence'], 2)
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Build Core DNA
    |--------------------------------------------------------------------------
    */
    private function buildDNA(): void
    {
        $this->assembleGenes();
        $this->calculateFitness();
        $this->determineLifeStage();
        $this->buildExecution();

        if ($this->dna['genes']['pattern'] === null) {
            $this->dna['status'] = 'WAIT';
        }

        $qualityScore = ($this->dna['fitness'] * 0.40) + ($this->dna['confidence'] * 0.40) + ($this->dna['trust'] * 0.20);

        if ($qualityScore >= 95) {
            $this->dna['dna_quality'] = "A+";
        } elseif ($qualityScore >= 90) {
            $this->dna['dna_quality'] = "A";
        } elseif ($qualityScore >= 80) {
            $this->dna['dna_quality'] = "B";
        } elseif ($qualityScore >= 70) {
            $this->dna['dna_quality'] = "C";
        } else {
            $this->dna['dna_quality'] = "D";
        }
        
        $this->dna['dna_id'] = md5(json_encode($this->dna['genes']) . microtime(true));
    }

    /*
    |--------------------------------------------------------------------------
    | Storage persistence
    |--------------------------------------------------------------------------
    */
    private function saveDNA(): void
    {
        $file = $this->storage . "/dna.json";
        if (!is_dir($this->storage)) {
            mkdir($this->storage, 0777, true);
        }
        file_put_contents($file, json_encode($this->dna, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function saveHistory(): void
    {
        $file = $this->storage . "/dna_history.json";
        $history = [];
        
        if (file_exists($file)) {
            $history = json_decode(file_get_contents($file), true);
            if (!is_array($history)) {
                $history = [];
            }
        }

        $history[] = [
            "datetime"   => date("Y-m-d H:i:s"),
            "dna_id"     => $this->dna["dna_id"],
            "fitness"    => $this->dna["fitness"],
            "confidence" => $this->dna["confidence"],
            "trust"      => $this->dna["trust"],
            "life_stage" => $this->dna["life_stage"],
            "execution"  => $this->dna["execution"]
        ];

        if (count($history) > 500) {
            $history = array_slice($history, -500);
        }

        file_put_contents($file, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /*
    |--------------------------------------------------------------------------
    | Run Engine
    |--------------------------------------------------------------------------
    */
    public function run(): array
    {
        $this->initialize();
        $this->buildDNA();
        $this->saveDNA();
        $this->saveHistory();
        return $this->dna;
    }
}

/*
|--------------------------------------------------------------------------
| RUNTIME EXECUTION
|--------------------------------------------------------------------------
*/
$dnaEngine = new DNAEngine();
$dna = $dnaEngine->run();

if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json');
}

echo json_encode($dna, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;