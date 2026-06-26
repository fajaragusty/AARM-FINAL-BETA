<?php

/**
|--------------------------------------------------------------------------
| AARM RC5
|--------------------------------------------------------------------------
| knowledge_optimizer.php
|--------------------------------------------------------------------------
| Purpose :
| - Compress Research Knowledge
| - Keep ONLY Best Candidate (TOP 3)
| - Prepare DNA Input
|--------------------------------------------------------------------------
*/

class KnowledgeOptimizer
{
    private array $knowledge = [];
    private array $context = [];
    private array $fusion = [];
    private array $feedback = [];
    private string $storage;

    public function __construct()
    {
        $this->storage   = dirname(__DIR__) . "/storage";
        $this->knowledge = $this->load($this->storage . '/knowledge.json');
        $this->feedback  = $this->load($this->storage . '/feedback_knowledge.json');
        $this->context   = $this->load($this->storage . '/context_knowledge.json');
        $this->fusion    = $this->load($this->storage . '/fusion_knowledge.json');
    }

    public function run(): void
    {
        // 1. Merge & Optimize Knowledge
        $mergedKnowledge = $this->mergeKnowledge($this->knowledge, $this->feedback);
        $knowledge       = $this->optimizeKnowledge($mergedKnowledge);
        
        // 2. Optimize Context & Fusion
        $context         = $this->optimizeContext($this->context);
        $fusion          = $this->optimizeFusion($this->fusion);

        // 3. Save to Storage
        $this->save($this->storage . '/knowledge_best.json', $knowledge);
        $this->save($this->storage . '/context_best.json', $context);
        $this->save($this->storage . '/fusion_best.json', $fusion);

        // 4. Clean & Informative Console Output
        echo "==========================================" . PHP_EOL;
        echo "        AARM RC5 KNOWLEDGE OPTIMIZER      " . PHP_EOL;
        echo "==========================================" . PHP_EOL;
        echo "Research Input  : " . count($this->knowledge) . PHP_EOL;
        echo "Feedback Input  : " . count($this->feedback) . PHP_EOL;
        echo "Merged Input    : " . count($mergedKnowledge) . PHP_EOL;
        echo "------------------------------------------" . PHP_EOL;
        echo "Knowledge Best  : " . count($knowledge) . PHP_EOL;
        echo "Context Best    : " . count($context) . PHP_EOL;
        echo "Fusion Best     : " . count($fusion) . PHP_EOL;
        echo "==========================================" . PHP_EOL;
        echo "Status          : Optimization Finished Successfully!" . PHP_EOL;
    }

    /*
    |--------------------------------------------------------------------------
    | optimizeKnowledge() - TOP 3 PER PATTERN
    |--------------------------------------------------------------------------
    */
    private function optimizeKnowledge(array $rows): array
    {
        $groups = [];

        foreach ($rows as $row) {
            // Jika row langsung berisi data pattern (Flat Array)
            if (is_array($row) && isset($row['pattern'])) {
                $row['optimizer_score'] = $this->calculateScore($row);
                $groups[$row['pattern']][] = $row;
                continue;
            }

            // Jika row berupa nested group (Multi-dimensional Array)
            if (is_array($row)) {
                foreach ($row as $item) {
                    if (!is_array($item) || !isset($item['pattern'])) {
                        continue;
                    }

                    $item['optimizer_score'] = $this->calculateScore($item);
                    $groups[$item['pattern']][] = $item;
                }
            }
        }

        $best = [];
        foreach ($groups as $pattern => $list) {
            usort($list, function ($a, $b) {
                return $b['optimizer_score'] <=> $a['optimizer_score'];
            });

            $best[$pattern] = array_slice($list, 0, 3);
        }

        ksort($best);
        return $best;
    }

    /*
    |--------------------------------------------------------------------------
    | optimizeContext() - TOP 3 PER CONTEXT (Fixing Potential Undefined Keys)
    |--------------------------------------------------------------------------
    */
    private function optimizeContext(array $rows): array
    {
        $groups = [];

        foreach ($rows as $row) {
            // Validasi untuk memastikan $row adalah array agar tidak error
            if (!is_array($row)) {
                continue;
            }

            $key = ($row['pattern'] ?? '') . "|" .
                   ($row['trend'] ?? '') . "|" .
                   ($row['market_state'] ?? '') . "|" .
                   ($row['rsi_zone'] ?? '');

            $row['optimizer_score'] = $this->calculateScore($row);
            $groups[$key][] = $row;
        }

        $best = [];
        foreach ($groups as $key => $list) {
            usort($list, function ($a, $b) {
                return $b['optimizer_score'] <=> $a['optimizer_score'];
            });
            $best[$key] = array_slice($list, 0, 3);
        }

        ksort($best);
        return $best;
    }

    /*
    |--------------------------------------------------------------------------
    | optimizeFusion() - TOP 3 PER PATTERN + DIGIT
    |--------------------------------------------------------------------------
    */
    private function optimizeFusion(array $rows): array
    {
        $groups = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $key = ($row['pattern'] ?? '') . "|" . ($row['digit'] ?? '');
            $score = (float)($row['fusion_score'] ?? 0);

            if (($row['status'] ?? '') === 'AGREEMENT') {
                $score += 5;
            }

            $row['optimizer_score'] = round($score, 2);
            $groups[$key][] = $row;
        }

        $best = [];
        foreach ($groups as $key => $list) {
            usort($list, function ($a, $b) {
                return $b['optimizer_score'] <=> $a['optimizer_score'];
            });
            $best[$key] = array_slice($list, 0, 3);
        }

        ksort($best);
        return $best;
    }

    /*
    |--------------------------------------------------------------------------
    | mergeKnowledge()
    |--------------------------------------------------------------------------
    */
    private function mergeKnowledge(array $research, array $feedback): array
    {
        if (empty($feedback)) {
            return $research;
        }

        // Jalur kembali untuk RC6 jika mapping akan diimplementasikan nanti
        return $research;
    }

    /* ========================================================= */
    private function calculateScore(array $row): float
    {
        $wr      = $row['wr'] ?? 0;
        $samples = $row['samples'] ?? 1;

        $sampleWeight = min(1, $samples / 250);
        $score        = ($wr * 0.75) + ($sampleWeight * 25);

        return round($score, 2);
    }

    /* ========================================================= */
    private function load(string $file): array
    {
        if (!file_exists($file)) {
            return [];
        }

        $json = file_get_contents($file);

        if (trim($json) === '') {
            return [];
        }

        $data = json_decode($json, true);

        if (!is_array($data)) {
            return [];
        }

        return $data;
    }

    /* ========================================================= */
    private function save(string $file, array $data): void
    {
        file_put_contents(
            $file,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}

/* ============================================================= */
$optimizer = new KnowledgeOptimizer();
$optimizer->run();