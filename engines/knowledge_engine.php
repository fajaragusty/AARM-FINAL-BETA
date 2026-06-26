<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA ADAPTIVE RESEARCH MACHINE
|--------------------------------------------------------------------------
| KNOWLEDGE ENGINE RC5
|--------------------------------------------------------------------------
| Purpose: Convert Research into Living Knowledge
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);
date_default_timezone_set('Asia/Jakarta');

// =========================================================================
// ENVIRONMENT INITIALIZATION (Perbaikan: Inisialisasi Path yang Hilang)
// =========================================================================
$root          = dirname(__DIR__);
$storage       = $root . '/storage';
$knowledgeFile = $storage . '/knowledge.json';

/*
|--------------------------------------------------------------------------
| RC5 Research Sources
|--------------------------------------------------------------------------
| Ambil hanya file research yang benar-benar diproduksi pipeline.
|--------------------------------------------------------------------------
*/
$researchFiles = [];

if (is_dir($storage)) {
    foreach (glob($storage . '/*_research.json') as $file) {
        if (basename($file) === 'research_ticks.json') {
            continue;
        }
        $researchFiles[] = $file;
    }
}

sort($researchFiles);

echo PHP_EOL;
echo "Research Sources : " . count($researchFiles) . PHP_EOL;
foreach ($researchFiles as $file) {
    echo " - " . basename($file) . PHP_EOL;
}
echo PHP_EOL;

// Load basis pengetahuan yang sudah ada
$knowledge = [];
if (file_exists($knowledgeFile)) {
    $knowledge = json_decode(file_get_contents($knowledgeFile), true) ?: [];
}

// Proses setiap file riset yang ditemukan
foreach ($researchFiles as $file) {
    if (!file_exists($file)) {
        continue;
    }

    $data = json_decode(file_get_contents($file), true) ?: [];
    if (empty($data)) {
        echo "[SKIP] " . basename($file) . " (EMPTY)" . PHP_EOL;
        continue;
    }

    $type = strtolower(basename($file, '_research.json'));
    echo "[LOAD] " . basename($file) . " : " . count($data) . " record" . PHP_EOL;

    foreach ($data as $item) {
        if (!is_array($item)) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Hash Key Generator (Consistent Mutating Row)
        |--------------------------------------------------------------------------
        */
        $patternIdentifier = $item['pattern'] ?? md5(json_encode($item));
        $key               = $type . '_' . md5($patternIdentifier);

        // Inisialisasi data baru jika belum ada di database knowledge
        if (!isset($knowledge[$key])) {
            $knowledge[$key] = [
                'id'         => $key,
                'type'       => $type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'age'        => 0,
                'status'     => 'DISCOVERED',
                'evolution'  => [],
                'pattern'    => $item['pattern'] ?? null,
                'direction'  => $item['direction'] ?? 'WAIT',
                'duration'   => $item['duration'] ?? 3,
                'win'        => $item['win'] ?? 0,
                'loss'       => $item['loss'] ?? 0,
                'samples'    => $item['samples'] ?? ($item['total'] ?? 0),
                'wr'         => $item['wr'] ?? ($item['trust'] ?? 0),
                'trust'      => $item['trust'] ?? 50,
                'data'       => []
            ];
        }

        // Update data yang sudah ada (Living Knowledge Mutation)
        $knowledge[$key]['age']++;
        $knowledge[$key]['updated_at'] = date('Y-m-d H:i:s');
        $knowledge[$key]['pattern']    = $item['pattern'] ?? $knowledge[$key]['pattern'];
        $knowledge[$key]['direction']  = $item['direction'] ?? $knowledge[$key]['direction'];
        $knowledge[$key]['duration']   = $item['duration'] ?? $knowledge[$key]['duration'];
        $knowledge[$key]['win']        = $item['win'] ?? $knowledge[$key]['win'];
        $knowledge[$key]['loss']       = $item['loss'] ?? $knowledge[$key]['loss'];
        
        $knowledge[$key]['samples']    = $item['samples'] ?? ($item['total'] ?? $knowledge[$key]['samples']);
        $knowledge[$key]['wr']         = $item['wr'] ?? ($item['trust'] ?? $knowledge[$key]['wr']);
        $knowledge[$key]['trust']      = $item['trust'] ?? $knowledge[$key]['trust'];
        $knowledge[$key]['data']       = $item;

        // Ambil nilai tervalidasi untuk kalkulasi status kasta
        $trust   = $knowledge[$key]['trust'];
        $samples = $knowledge[$key]['samples'];

        if ($trust >= 90 && $samples >= 300) {
            $status = 'ELITE';
        } elseif ($trust >= 75) {
            $status = 'SURVIVOR';
        } elseif ($trust >= 60) {
            $status = 'CANDIDATE';
        } elseif ($trust >= 45) {
            $status = 'WATCHLIST';
        } else {
            $status = 'DECAY';
        }

        $knowledge[$key]['status'] = $status;

        // Catat riwayat log perubahan ke dalam Evolution Array
        $knowledge[$key]['evolution'][] = [
            'time'   => date('Y-m-d H:i:s'),
            'trust'  => $trust,
            'status' => $status
        ];

        // Batasi histori evolution maksimal 100 log (FIFO)
        if (count($knowledge[$key]['evolution']) > 100) {
            array_shift($knowledge[$key]['evolution']);
        }
    }
}

// Filter baris data kosong atau tidak valid
$knowledge = array_filter($knowledge, function ($row) {
    return is_array($row) && isset($row['status']);
});

// Sorting Multilevel: Status Rank -> Trust -> Samples
uasort($knowledge, function ($a, $b) {
    $rank = [
        'ELITE'      => 5,
        'SURVIVOR'   => 4,
        'CANDIDATE'  => 3,
        'WATCHLIST'  => 2,
        'DECAY'      => 1,
        'DISCOVERED' => 0
    ];

    $rankA = $rank[$a['status']] ?? 0;
    $rankB = $rank[$b['status']] ?? 0;

    // 1. Urutkan berdasarkan Kasta Status tertinggi
    if ($rankA !== $rankB) {
        return $rankB <=> $rankA;
    }

    // 2. Jika Status sama, urutkan berdasarkan nilai Trust tertinggi
    $trustA = $a['trust'] ?? 0;
    $trustB = $b['trust'] ?? 0;
    if ($trustA !== $trustB) {
        return $trustB <=> $trustA;
    }

    // 3. Jika Trust masih sama, urutkan berdasarkan jumlah Sempel terbanyak
    return ($b['samples'] ?? 0) <=> ($a['samples'] ?? 0);
});

// Simpan kembali data knowledge yang telah diperbarui
file_put_contents(
    $knowledgeFile,
    json_encode($knowledge, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

echo "KNOWLEDGE ENGINE COMPLETE\n";