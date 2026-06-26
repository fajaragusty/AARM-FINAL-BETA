<?php
/**
 * AARM RC5 - TACTICAL OPERATIONS CORES MATRIX TERMINAL
 * Designed exactly via Blueprint Workflow Specifications v5.0 & GitHub Repository Base
 */

$storagePath = dirname(__DIR__) . '/storage/';
$enginePath  = dirname(__DIR__) . '/engines/';

// --- 1. RUNTIME OVERVIEW MAPPING ---
$runtimeStatus   = json_decode(@file_get_contents($storagePath . 'runtime_status.json'), true) ?: [];
$collectorStatus = json_decode(@file_get_contents($storagePath . 'collector_status.json'), true) ?: [];

/* ============================================================
 * RC5 LEARNING STATE
 * ============================================================ */
function jsonModified($file) {
    return file_exists($file) ? filemtime($file) : 0;
}

$learningState = [
    'research'  => jsonModified($storagePath . 'pattern_research.json'),
    'knowledge' => jsonModified($storagePath . 'knowledge_best.json'),
    'brain'     => jsonModified($storagePath . 'brain_consensus_v4.json'),
    'signal'    => jsonModified($storagePath . 'signals.json'),
    'feedback'  => jsonModified($storagePath . 'feedback_summary.json'),
];

// --- 2. COLLECTOR ---
$ticks = json_decode(@file_get_contents($storagePath . 'historical_ticks.json'), true) ?: [];
$lastTick   = end($ticks);
$totalTicks = count($ticks);
$firstTick  = $ticks[0] ?? [];

$collectorHealth = [
    'total'      => $totalTicks,
    'first_time' => $firstTick['time'] ?? '-',
    'last_time'  => $lastTick['time'] ?? '-',
    'quote'      => $lastTick['quote'] ?? 0,
    'delay'      => $collectorStatus['delay'] ?? '-',
    'tick_rate'  => $collectorStatus['tick_rate'] ?? '-'
];

// --- 3. RESEARCH CENTER ---
$trendResearch     = json_decode(@file_get_contents($storagePath . 'trend_research.json'), true) ?: [];
$patternResearch   = json_decode(@file_get_contents($storagePath . 'pattern_research.json'), true) ?: [];
$digitResearch     = json_decode(@file_get_contents($storagePath . 'digit_research.json'), true) ?: [];
$last2Research     = json_decode(@file_get_contents($storagePath . 'last2_research.json'), true) ?: [];
$indicatorResearch = json_decode(@file_get_contents($storagePath . 'indicator_research.json'), true) ?: [];
$hybridResearch    = json_decode(@file_get_contents($storagePath . 'hybrid_research.json'), true) ?: [];

/* =====================================================
 * RC5 RESEARCH STATUS
 * ===================================================== */
function researchInfo($data) {
    if (!is_array($data)) {
        return [
            'samples' => 0,
            'wr'      => 0,
            'trust'   => 0,
            'updated' => '-'
        ];
    }
    return [
        'samples' => $data['samples'] ?? 0,
        'wr'      => $data['wr'] ?? 0,
        'trust'   => $data['trust'] ?? 0,
        'updated' => $data['updated_at'] ?? $data['updated'] ?? '-'
    ];
}

$trendInfo     = researchInfo($trendResearch);
$patternInfo   = researchInfo($patternResearch);
$digitInfo     = researchInfo($digitResearch);
$last2Info     = researchInfo($last2Research);
$indicatorInfo = researchInfo($indicatorResearch);
$hybridInfo    = researchInfo($hybridResearch);

// --- 4. KNOWLEDGE CENTER ---
$knowledgeBest = json_decode(@file_get_contents($storagePath . 'knowledge_best.json'), true) ?: [];

/* ==========================================================
 * RC5 KNOWLEDGE ANALYZER
 * ========================================================== */
$totalKnowledge = is_array($knowledgeBest) ? count($knowledgeBest) : 0;
$bestKnowledge  = $knowledgeBest[0] ?? [];

$knowledgeInfo = [
    'pattern'    => $bestKnowledge['pattern'] ?? '-',
    'samples'    => $bestKnowledge['samples'] ?? 0,
    'wr'         => $bestKnowledge['wr'] ?? 0,
    'trust'      => $bestKnowledge['trust'] ?? 0,
    'confidence' => $bestKnowledge['confidence'] ?? 0,
    'age'        => $bestKnowledge['research_age'] ?? $bestKnowledge['age'] ?? 0,
    'updated'    => $bestKnowledge['updated_at'] ?? '-'
];

// --- 5. KNOWLEDGE OPTIMIZER ---
$knowledgeOptimized = json_decode(@file_get_contents($storagePath . 'knowledge_optimized.json'), true) ?: [];

// --- 6. BRAIN CENTER ---
$brainConsensus = json_decode(@file_get_contents($storagePath . 'brain_consensus_v4.json'), true) ?: [];

/* ==========================================================
 * RC5 BRAIN ANALYZER
 * ========================================================== */
$brainInfo = [
    'decision'   => $brainConsensus['decision'] ?? 'WAIT',
    'confidence' => $brainConsensus['confidence'] ?? 0,
    'trust'      => $brainConsensus['trust'] ?? 0,
    'fitness'    => $brainConsensus['fitness'] ?? 0,
    'risk'       => $brainConsensus['risk'] ?? 'UNKNOWN',
    'dna'        => $brainConsensus['dna_quality'] ?? 'D',
    'knowledge'  => $brainConsensus['knowledge_count'] ?? 0,
    'updated'    => $brainConsensus['updated_at'] ?? '-'
];

// --- 7. SIGNAL ENGINE ---
$signals    = json_decode(@file_get_contents($storagePath . 'signals.json'), true) ?: [];
$lastSignal = end($signals);

/* ==========================================================
 * RC5 REASON ENGINE
 * Dashboard hanya membaca hasil engine
 * ========================================================== */
$reasonEngine = [
    'trend' => [
        'status' => ($trendInfo['trust'] ?? 0) >= 60 ? 'PASS' : 'FAIL',
        'trust'  => $trendInfo['trust'] ?? 0
    ],
    'pattern' => [
        'status' => ($patternInfo['trust'] ?? 0) >= 60 ? 'PASS' : 'FAIL',
        'trust'  => $patternInfo['trust'] ?? 0
    ],
    'digit' => [
        'status' => ($digitInfo['trust'] ?? 0) >= 60 ? 'PASS' : 'FAIL',
        'trust'  => $digitInfo['trust'] ?? 0
    ],
    'indicator' => [
        'status' => ($indicatorInfo['trust'] ?? 0) >= 60 ? 'PASS' : 'FAIL',
        'trust'  => $indicatorInfo['trust'] ?? 0
    ],
    'hybrid' => [
        'status' => ($hybridInfo['trust'] ?? 0) >= 60 ? 'PASS' : 'FAIL',
        'trust'  => $hybridInfo['trust'] ?? 0
    ],
    'brain' => [
        'status' => ($brainInfo['confidence'] ?? 0) >= 60 ? 'PASS' : 'FAIL'
    ]
];

$consensusPass = 0;
foreach($reasonEngine as $r){
    if($r['status']=='PASS'){
        $consensusPass++;
    }
}

// --- 8. PAPER ENGINE & 9. FEEDBACK SUMMARY ---
$paperTrades     = json_decode(@file_get_contents($storagePath . 'paper_trades.json'), true) ?: [];
$paperSummary    = json_decode(@file_get_contents($storagePath . 'paper_trade_summary.json'), true) ?: [];
$feedbackSummary = json_decode(@file_get_contents($storagePath . 'feedback_summary.json'), true) ?: [];
$feedbackHistory = json_decode(@file_get_contents($storagePath . 'feedback_history.json'), true) ?: [];

/* ==========================================================
 * RC5 FEEDBACK LEARNING ENGINE
 * ========================================================== */
$feedbackLearning = [
    'total_feedback' => count($feedbackHistory),
    'win' => $feedbackSummary['win'] ?? 0,
    'loss' => $feedbackSummary['loss'] ?? 0,
    'wr' => $feedbackSummary['wr'] ?? 0,
    'knowledge_sync' => $brainInfo['knowledge'] ?? 0,
    'brain_updated' => $brainInfo['updated'] ?? '-',
    'last_pattern' => $lastSignal['pattern'] ?? '-',
    'decision' => $brainInfo['decision'] ?? 'WAIT'
];

// --- 10. EXPERIENCE ENGINE ---
$experienceSummary = json_decode(@file_get_contents($storagePath . 'experience_summary.json'), true) ?: [];

/* ==========================================================
 * RC5 SURVIVOR EXPERIENCE
 * ========================================================== */
$survivor = [];
if(is_array($experienceSummary)){
    foreach($experienceSummary as $pattern=>$row){
        $wr = $row['wr'] ?? 0;
        $trust = $row['trust'] ?? 0;
        $cycle = $row['total'] ?? 0;

        if($wr>=55 && $trust>=60){
            $status='ELITE';
        }elseif($wr>=50){
            $status='ACTIVE';
        }else{
            $status='LEARNING';
        }

        $survivor[]=[
            'pattern'=>$pattern,
            'wr'=>$wr,
            'trust'=>$trust,
            'cycle'=>$cycle,
            'status'=>$status
        ];
    }
}

usort($survivor,function($a,$b){
    return $b['trust']<=>$a['trust'];
});

// --- 13. VALIDATOR RC5 CHECK ---
/* ==========================================================
 * RC5 PIPELINE VALIDATOR
 * ========================================================== */
function pipelineStatus(string $file, int $maxAge = 15): array
{
    global $storagePath;
    $full = $storagePath . $file;

    if (!file_exists($full)) {
        return [
            'status' => 'MISSING',
            'color'  => 'var(--color-put)',
            'age'    => '-'
        ];
    }

    $age = time() - filemtime($full);

    if ($age <= $maxAge) {
        return [
            'status' => 'LIVE',
            'color'  => 'var(--color-call)',
            'age'    => $age
        ];
    }

    if ($age <= 60) {
        return [
            'status' => 'STALE',
            'color'  => 'var(--color-wait)',
            'age'    => $age
        ];
    }

    return [
        'status' => 'STOP',
        'color'  => 'var(--color-put)',
        'age'    => $age
    ];
}

$pipeline = [
    'Collector'   => pipelineStatus('historical_ticks.json'),
    'Research'    => pipelineStatus('pattern_research.json'),
    'Knowledge'   => pipelineStatus('knowledge_best.json'),
    'Optimizer'   => pipelineStatus('knowledge_optimized.json'),
    'Brain'       => pipelineStatus('brain_consensus_v4.json'),
    'Signal'      => pipelineStatus('signals.json'),
    'PaperTrade'  => pipelineStatus('paper_trades.json'),
    'Feedback'    => pipelineStatus('feedback_summary.json'),
    'Experience'  => pipelineStatus('experience_summary.json')
];

$validatorReady = true;
foreach($pipeline as $p){
    if($p['status']!='LIVE'){
        $validatorReady=false;
    }
}

/* ==========================================================
 * RC5 LIVE WORKFLOW
 * ========================================================== */
$workflow = [
    'Collector'  => $pipeline['Collector'],
    'Research'   => $pipeline['Research'],
    'Knowledge'  => $pipeline['Knowledge'],
    'Optimizer'  => $pipeline['Optimizer'],
    'Brain'      => $pipeline['Brain'],
    'Signal'     => $pipeline['Signal'],
    'Feedback'   => $pipeline['Feedback'],
    'Experience' => $pipeline['Experience']
];

// --- 15. TELEGRAM ENGINE STATUS ---
$telegramStatus = json_decode(@file_get_contents($storagePath . 'telegram_status.json'), true) ?: [];

// System Styling Rules
$decision = $brainConsensus['decision'] ?? 'WAIT';
$modeClass = 'wait';
if (strpos($decision, 'CALL') !== false) { $modeClass = 'call'; } 
elseif (strpos($decision, 'PUT') !== false) { $modeClass = 'put'; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AARM RC5 // Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-body: #0b0f17;
            --bg-card: #121824;
            --border: #1e293b;
            --text-main: #f1f5f9;
            --text-muted: #64748b;
            --color-call: #10b981;
            --color-put: #ef4444;
            --color-wait: #f59e0b;
            --color-cyan: #06b6d4;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            padding: 20px;
        }
        .mono { font-family: 'JetBrains Mono', monospace; font-size: 12px; }
        
        /* Top Navigation Header */
        .top-bar {
            display: flex; justify-content: space-between; align-items: center;
            background: var(--bg-card); border: 1px solid var(--border);
            padding: 14px 20px; border-radius: 8px; margin-bottom: 16px;
        }
        .top-title { font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px; }
        .top-title span { color: var(--text-muted); font-weight: 400; font-size: 11px; }

        /* Main Compact Layout Grid */
        .main-grid {
            display: grid; grid-template-columns: repeat(12, 1fr); gap: 16px;
        }
        .panel-card {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 8px; padding: 18px; display: flex; flex-direction: column; gap: 12px;
        }
        .span-4 { grid-column: span 4; }
        .span-8 { grid-column: span 8; }
        .span-12 { grid-column: span 12; }
        
        @media (max-width: 1024px) {
            .span-4, .span-8 { grid-column: span 12; }
        }

        .card-header {
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
            color: var(--text-muted); display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid rgba(30, 41, 59, 0.5); padding-bottom: 8px;
        }

        /* Generic Data Row styling */
        .row-item { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid rgba(255,255,255,0.02); }
        .row-item:last-child { border-bottom: none; }
        .row-item span { color: var(--text-muted); }
        .row-item b { color: var(--text-main); font-weight: 500; }

        /* Realtime Price & State Widget */
        .hero-widget {
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(0,0,0,0.2); padding: 12px; border-radius: 6px; border: 1px solid var(--border);
        }
        .hero-price { font-size: 26px; font-weight: 700; letter-spacing: -0.03em; }
        .hero-state { font-size: 14px; font-weight: 700; text-transform: uppercase; }
        
        .call { color: var(--color-call) !important; }
        .put { color: var(--color-put) !important; }
        .wait { color: var(--color-wait) !important; }

        /* Tables Minimalistic */
        .table-scroll { overflow-x: auto; width: 100%; border-radius: 6px; border: 1px solid var(--border); }
        .clean-table { width: 100%; border-collapse: collapse; text-align: left; }
        .clean-table th { background: #161f30; padding: 8px 10px; font-size: 11px; color: var(--text-muted); font-weight: 600; }
        .clean-table td { padding: 8px 10px; border-bottom: 1px solid var(--border); color: var(--text-main); }
        .clean-table tr:last-child td { border-bottom: none; }

        /* Badges & Flex Mini items */
        .badge { font-size: 10px; padding: 1px 6px; border-radius: 4px; font-weight: 600; }
        .badge-live { background: rgba(16,185,129,0.1); color: var(--color-call); }
        
        .flex-box { display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 8px; }
        .mini-box { background: rgba(0,0,0,0.15); padding: 8px; border-radius: 4px; border: 1px solid var(--border); text-align: center; }
        .mini-box span { display: block; font-size: 10px; color: var(--text-muted); margin-bottom: 2px; text-transform: uppercase; }
        .mini-box b { font-size: 12px; }

        /* Action Controls Buttons */
        .btn-group { display: flex; gap: 8px; }
        .btn {
            flex: 1; border: none; padding: 10px; border-radius: 4px; font-weight: 600; font-size: 11px;
            cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 4px; color: #fff;
            transition: opacity 0.2s; text-decoration: none;
        }
        .btn:hover { opacity: 0.9; }
        .btn-call { background: var(--color-call); }
        .btn-put { background: var(--color-put); }
        .btn-sec { background: rgba(30, 41, 59, 0.6); border: 1px solid var(--border); color: var(--text-main); }

        /* Horizontal Pipeline flow mapping */
        .pipeline-flow { display: flex; gap: 8px; align-items: center; overflow-x: auto; padding: 4px 0; }
        .flow-node { padding: 6px 12px; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 4px; font-size: 11px; white-space: nowrap; }
    </style>
</head>
<body>

    <div class="top-bar">
        <div class="top-title">
            <i class="fa-solid fa-bolt" style="color:var(--color-cyan)"></i> AARM RC5 CORES MATRIX <span>// ENGINE TERMINAL</span>
        </div>
        <div class="mono" style="color: var(--color-cyan); font-size: 11px;">
            <i class="fa-solid fa-circle fa-pulse" style="font-size:8px;"></i> ATTACHED
        </div>
    </div>

    <div class="main-grid">
        
        <div class="panel-card span-4">
            <div class="card-header">1. SYSTEM ENGINE STATUS <i class="fa-solid fa-circle-nodes"></i></div>
            
            <div class="hero-widget">
                <div>
                    <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase;">Live Market Stream</div>
                    <div class="hero-price mono" id="live_market"><?= $lastTick['quote'] ?? '0.0000' ?></div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase;">Decision State</div>
                    <div class="hero-state <?= $modeClass ?>"><?= $brainInfo['decision'] ?></div>
                </div>
            </div>

            <div class="mono">
                <div class="row-item"><span>Sys State / Token:</span><b><?= $runtimeStatus['status'] ?? 'RUNNING' ?> / <?= substr($runtimeStatus['session_id'] ?? md5(time()), 0, 8) ?></b></div>
                <div class="row-item"><span>Uptime Matrix:</span><b id="live_runtime" style="color:var(--color-cyan)"><?= $runtimeStatus['uptime'] ?? '00:00:00' ?></b></div>
                <div class="row-item"><span>Total Tick Stock:</span><b id="live_tick_count"><?= $collectorHealth['total'] ?></b></div>
                <div class="row-item"><span>Collector Health:</span><b id="collector_health" class="call">STREAMING</b></div>
                <div class="row-item"><span>Last Sync Time:</span><b id="collector_last_tick"><?= $collectorHealth['last_time'] ?></b></div>
                <div class="row-item"><span>Buffer Allocated:</span><b class="wait"><?= @round(filesize($storagePath.'historical_ticks.json')/1024,1) ?> KB</b></div>
                <div class="row-item"><span>Telegram Driver:</span><b class="call"><?= $telegramStatus['status'] ?? 'ONLINE' ?></b></div>
                <div class="row-item"><span>Broadcast Dispatched:</span><b>Sg:<?= count($signals) ?> / Trd:<?= count($paperTrades) ?></b></div>
            </div>
            
            <div class="card-header" style="margin-top:4px; padding-bottom:4px;">PIPELINE TIMESTAMPS</div>
            <div class="mono" style="font-size:11px;">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:4px 12px;">
                    <div>Research: <b><?= $learningState['research'] ? date('H:i:s',$learningState['research']) : '-' ?></b></div>
                    <div>Knowledge: <b><?= date('H:i:s',$learningState['knowledge']) ?></b></div>
                    <div>Brain Matrix: <b><?= date('H:i:s',$learningState['brain']) ?></b></div>
                    <div>Feedback Sync: <b><?= date('H:i:s',$learningState['feedback']) ?></b></div>
                </div>
            </div>
        </div>

        <div class="panel-card span-4">
            <div class="card-header">2. BRAIN & KNOWLEDGE MATRIX <i class="fa-solid fa-brain"></i></div>
            
            <div class="flex-box mono">
                <div class="mini-box"><span>Confidence</span><b class="call"><?= $brainInfo['confidence'] ?>%</b></div>
                <div class="mini-box"><span>Trust Level</span><b><?= $brainInfo['trust'] ?></b></div>
                <div class="mini-box"><span>DNA Grade</span><b style="color:var(--color-cyan)"><?= $brainInfo['dna'] ?></b></div>
                <div class="mini-box"><span>Consensus</span><b class="wait"><?= $consensusPass ?>/6</b></div>
            </div>

            <div class="mono">
                <div class="row-item"><span>Best Matched Pattern:</span><b style="color:var(--color-cyan)"><?= $knowledgeInfo['pattern'] ?></b></div>
                <div class="row-item"><span>Pattern Base WR / Trust:</span><b><?= $knowledgeInfo['wr'] ?>% / <?= $knowledgeInfo['trust'] ?></b></div>
                <div class="row-item"><span>Knowledge Bank Pool:</span><b><?= $totalKnowledge ?> items</b></div>
                <div class="row-item"><span>Optimizer Accept/Reject:</span><b><span class="call"><?= $knowledgeOptimized['accepted'] ?? 0 ?></span> / <span class="put"><?= $knowledgeOptimized['rejected'] ?? 0 ?></span></b></div>
                <div class="row-item"><span>Risk Assessment Level:</span><b class="wait"><?= $brainInfo['risk'] ?></b></div>
                <div class="row-item"><span>Brain Consensus Age:</span><span><?= $brainInfo['updated'] ?></span></div>
            </div>

            <div class="card-header" style="margin-top:2px; padding-bottom:4px;">REASON CORE ENGINE MAPPING</div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;" class="mono text-center">
                <?php foreach(['trend', 'pattern', 'digit', 'indicator', 'hybrid'] as $eng): ?>
                    <div style="background: rgba(255,255,255,0.02); padding: 5px; border-radius: 4px; border: 1px solid var(--border); text-align:center; font-size:11px;">
                        <span style="color:var(--text-muted); display:block; font-size:9px;"><?= strtoupper($eng) ?></span>
                        <b class="<?= $reasonEngine[$eng]['status']=='PASS' ? 'call' : 'put' ?>"><?= $reasonEngine[$eng]['status'] ?></b>
                    </div>
                <?php endforeach; ?>
                <div style="background: rgba(255,255,255,0.02); padding: 5px; border-radius: 4px; border: 1px solid var(--border); text-align:center; font-size:11px;">
                    <span style="color:var(--text-muted); display:block; font-size:9px;">BRAIN</span>
                    <b class="<?= $reasonEngine['brain']['status']=='PASS' ? 'call' : 'put' ?>"><?= $reasonEngine['brain']['status'] ?></b>
                </div>
            </div>
        </div>

        <div class="panel-card span-4">
            <div class="card-header">3. REALTIME RESEARCH CORE STREAM <i class="fa-solid fa-flask"></i></div>
            <div class="table-scroll">
                <table class="clean-table mono">
                    <thead>
                        <tr>
                            <th>Core Engine</th>
                            <th>Samples</th>
                            <th>WR %</th>
                            <th>Trust</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $engines = ['Trend' => $trendInfo, 'Pattern' => $patternInfo, 'Digit' => $digitInfo, 'Last2' => $last2Info, 'Indicator' => $indicatorInfo, 'Hybrid' => $hybridInfo];
                        foreach($engines as $name => $info): 
                        ?>
                        <tr>
                            <td><b><?= $name ?></b></td>
                            <td><?= $info['samples'] ?></td>
                            <td class="call"><?= $info['wr'] ?>%</td>
                            <td><?= $info['trust'] ?></td>
                            <td><span class="badge badge-live">LIVE</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel-card span-12">
            <div class="card-header">4. SIGNAL ENGINE CONTROLLER & TRANSACTION LOGS <i class="fa-solid fa-bolt"></i></div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; align-items: start;">
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <div class="mono" style="background:rgba(0,0,0,0.15); padding:10px; border-radius:6px; border:1px solid var(--border);">
                        <div style="font-weight:600; color:var(--text-main); margin-bottom:6px; font-size:11px; text-transform:uppercase;">Feedback Control Center</div>
                        <div class="row-item"><span>Target Pattern Signature:</span><b><?= $lastSignal['pattern'] ?? '-' ?></b></div>
                        <div class="row-item"><span>Dispatched Direction Strategy:</span><b class="<?= ($lastSignal['direction'] ?? '') === 'CALL' ? 'call':'put' ?>"><?= $lastSignal['direction'] ?? 'NONE' ?></b></div>
                        <div class="row-item"><span>Success Ratio (Feedback WR):</span><b class="call"><?= $feedbackSummary['wr'] ?? '0%' ?></b></div>
                        <div class="row-item"><span>Total History Node Saved:</span><b><?= $feedbackLearning['total_feedback'] ?> Items</b></div>
                    </div>
                    <div class="btn-group">
                        <button onclick="sendResult('WIN')" class="btn btn-call"><i class="fa-solid fa-circle-plus"></i> Manual WIN</button>
                        <button onclick="sendResult('LOSS')" class="btn btn-put"><i class="fa-solid fa-circle-minus"></i> Manual LOSS</button>
                    </div>
                    <div id="feedback_status" class="mono" style="text-align:center; font-weight:700; color:var(--color-cyan); min-height:14px; font-size:11px;"></div>
                </div>

                <div class="table-scroll">
                    <table class="clean-table mono">
                        <thead>
                            <tr><th colspan="4" style="text-align:center;">SURVIVOR MATRIX MATRIX HEROES (TOP 3)</th></tr>
                            <tr><th>Pattern</th><th>WR</th><th>Trust</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0; foreach ($survivor as $row): if ($i++ >= 3) break;
                            $c = ($row['status'] == 'ELITE') ? 'var(--color-call)' : (($row['status'] == 'LEARNING') ? 'var(--color-put)' : 'var(--color-wait)');
                            ?>
                            <tr>
                                <td><b><?= $row['pattern'] ?></b></td>
                                <td class="call"><?= $row['wr'] ?>%</td>
                                <td><?= $row['trust'] ?></td>
                                <td style="color:<?= $c ?>; font-weight:bold; font-size:10px;"><?= $row['status'] ?></td>
                            </tr>
                            <?php endforeach; if(empty($experienceSummary)) { echo '<tr><td colspan="4" style="text-align:center;color:var(--text-muted);">Empty Node</td></tr>'; } ?>
                        </tbody>
                    </table>
                </div>

                <div class="mono" style="background:rgba(0,0,0,0.1); padding:10px; border-radius:6px; border:1px solid var(--border); font-size:11px;">
                    <div style="font-weight:600; color:var(--text-main); margin-bottom:4px; font-size:10px; text-transform:uppercase;">HEALTH CHECK VALIDATOR</div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:2px 10px;">
                        <?php foreach($pipeline as $name=>$row): ?>
                            <div><?= $name ?>: <b style="color:<?= $row['color'] ?>"><?= $row['status'] ?></b></div>
                        <?php endforeach; ?>
                    </div>
                    <hr style="border:0; border-top:1px solid var(--border); margin:6px 0;">
                    <div class="row-item" style="padding:0;"><span>RC5 Status Validator Verify:</span><b class="<?= $validatorReady?'call':'put' ?>"><?= $validatorReady ? 'READY / VERIFIED' : 'PIPELINE_BROKEN' ?></b></div>
                </div>
            </div>

            <div class="table-scroll" style="margin-top:6px;">
                <table class="clean-table mono">
                    <thead>
                        <tr>
                            <th>Entry Timestamp</th>
                            <th>Signature</th>
                            <th>Action</th>
                            <th>Conf</th>
                            <th>RSI</th>
                            <th>Trend</th>
                            <th>EMA 9/21</th>
                            <th>Risk</th>
                            <th>DNA</th>
                            <th>Price Target</th>
                            <th style="text-align:right;">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $renderTrades = array_reverse(array_slice($paperTrades, -4));
                        foreach($renderTrades as $t): 
                            $res = $t['result'] ?? 'OPEN';
                            $resStyle = ($res === 'WIN') ? 'color: var(--color-call); font-weight:700;' : (($res === 'LOSS') ? 'color: var(--color-put); font-weight:700;' : 'color: var(--text-muted);');
                        ?>
                        <tr>
                            <td style="color:var(--text-muted);"><?= $t['entry_datetime'] ?? date('Y-m-d H:i:s') ?></td>
                            <td><b><?= $t['pattern'] ?? 'UNKNOWN' ?></b></td>
                            <td class="<?= ($t['direction'] ?? '') === 'CALL' ? 'call':'put' ?>" style="font-weight:700;"><?= $t['direction'] ?? 'WAIT' ?></td>
                            <td><?= $t['confidence'] ?? 0 ?>%</td>
                            <td><?= $t['rsi'] ?? 50 ?></td>
                            <td><?= $t['trend'] ?? 'SIDEWAYS' ?></td>
                            <td><?= isset($t['ema9']) ? round($t['ema9'], 2) . '/' . round($t['ema21'], 2) : '-' ?></td>
                            <td><span class="badge" style="background:rgba(255,255,255,0.05);"><?= $t['risk'] ?? 'HIGH' ?></span></td>
                            <td style="color:var(--color-cyan); font-weight:bold;"><?= $t['dna_quality'] ?? 'D' ?></td>
                            <td><b><?= $t['entry_price'] ?? '0' ?></b></td>
                            <td style="text-align:right; <?= $resStyle ?>"><?= $res ?></td>
                        </tr>
                        <?php endforeach; if(empty($paperTrades)) { echo '<tr><td colspan="11" style="text-align:center; color:var(--text-muted); padding:10px;">Operational transaction log empty</td></tr>'; } ?>
                    </tbody>
                </table>
            </div>

            <div class="btn-group" style="margin-top:4px;">
                <a href="../reset_rc5.php" class="btn btn-sec mono" style="color:var(--color-put); border-color:rgba(239,68,68,0.2);"><i class="fa-solid fa-power-off"></i> Hard Reset Runtime Matrices</a>
                <a href="../engines/validator_rc5.php" class="btn btn-sec mono"><i class="fa-solid fa-gears"></i> Deploy Structural Validator</a>
                <a href="../run_runtime_chain.php" class="btn btn-sec mono" style="color:var(--color-cyan); border-color:rgba(6,182,212,0.2);"><i class="fa-solid fa-network-wired"></i> Force Instant Cascade Loop</a>
            </div>
        </div>

        <div class="panel-card span-12">
            <div class="card-header">5. ADAPTIVE PIPELINE WORKFLOW & TELEMETRY STREAM <i class="fa-solid fa-diagram-project"></i></div>
            <div class="pipeline-flow mono">
                <?php $first=true; foreach($workflow as $name=>$node): if(!$first){ echo '<div style="color:var(--text-muted);">→</div>'; } $first=false; ?>
                    <div class="flow-node" style="border-color:<?= $node['color'] ?>;">
                        <span style="color:<?= $node['color'] ?>; font-weight:bold;"><?= $name ?></span>: <small style="color:var(--text-main)"><?= $node['status'] ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 8px; font-size:11px;" class="mono text-muted">
                <div>Memory Res: <b><?= count($patternResearch) ?></b></div>
                <div>Knowledge Base: <b><?= $totalKnowledge ?></b></div>
                <div>Signal Q: <b><?= count($signals) ?></b></div>
                <div>Experience Pool: <b><?= count($experienceSummary) ?></b></div>
                <div>Overall Node State: <b class="<?= $validatorReady?'call':'put' ?>"><?= $validatorReady ? 'ADAPTIVE' : 'WAITING' ?></b></div>
            </div>
        </div>

    </div>

    <script src="../websocket.js"></script>
    <script>
        let activePrice = null;
        let activeTicks = parseInt('<?= count($ticks) ?>') || 0;
         
        // Core AJAX High Frequency Interface Polling
        setInterval(async () => {
            try {
                const r = await fetch('../engines/collector.php');
                const dataset = await r.json();
         
                // 1. Live Market Price Flasher
                const priceHUD = document.getElementById('live_market');
                if(priceHUD && dataset.last && dataset.last.quote !== undefined) {
                    const price = parseFloat(dataset.last.quote);
                    priceHUD.innerText = price.toFixed(4);
                    if(activePrice !== null) {
                        if(price > activePrice) { priceHUD.className = 'hero-price mono call'; }
                        else if(price < activePrice) { priceHUD.className = 'hero-price mono put'; }
                        else { priceHUD.className = 'hero-price mono'; }
                    }
                    activePrice = price;
                }
         
                // 2. Continuous Tick Engine Safety Stream Counter
                const tickHUD = document.getElementById('live_tick_count');
                if(tickHUD) {
                    const actualTicks = dataset.total !== undefined ? dataset.total : (dataset.count !== undefined ? dataset.count : activeTicks);
                    tickHUD.innerText = actualTicks;
                    
                    const lastTickHUD = document.getElementById('collector_last_tick');
                    if(lastTickHUD && dataset.last){
                        lastTickHUD.innerText = dataset.last.time ?? '-';
                    }
         
                    const healthHUD = document.getElementById('collector_health');
                    if(healthHUD){
                        healthHUD.innerText='STREAMING';
                        healthHUD.className='call';
                    }
                    activeTicks = actualTicks;
                }
         
            } catch(error){
                const h = document.getElementById('collector_health');
                if(h){
                    h.innerText='OFFLINE';
                    h.className='put';
                }
            }
        }, 1000);
         
        // Supervisor Interaction Protocol Handler
        async function sendResult(resultValue) {
            const currentSignal = <?= json_encode($lastSignal ?? []) ?>;
            if(!currentSignal.time) {
                document.getElementById('feedback_status').innerText = 'ERROR: PACKET CONTEXT LACKS TIME SIGNATURE';
                return;
            }
            try {
                const r = await fetch('../save_feedback.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ signal_time: currentSignal.time, result: resultValue })
                });
                const data = await r.json();
                document.getElementById('feedback_status').innerHTML = `<i class="fa-solid fa-circle-check"></i> DISPATCHED: ${resultValue}`;
                setTimeout(() => { location.reload(); }, 750);
            } catch(e) {
                document.getElementById('feedback_status').innerText = 'OPERATIONAL CONTROLLER INTERRUPT';
            }
        }
    </script>
</body>
</html>