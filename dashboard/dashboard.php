<?php
/*
|--------------------------------------------------------------------------
| AARM RC5 DASHBOARD
| PART 1
| LIVE COLLECTOR ENGINE
|--------------------------------------------------------------------------
| Dashboard Viewer Only
| Reads collector output only
| No business logic
| No signal generation
|--------------------------------------------------------------------------
*/

date_default_timezone_set('Asia/Jakarta');

define('ROOT', dirname(__DIR__));
define('STORAGE', ROOT . '/storage/');

header("Cache-Control: no-cache, no-store, must-revalidate");

function loadJson(string $file): array
{
    $path = STORAGE . $file;
    if (!file_exists($path)) {
        return [];
    }
    $json = json_decode(file_get_contents($path), true);
    return is_array($json) ? $json : [];
}

$ticks = loadJson('historical_ticks.json');
$totalTicks = count($ticks);

$lastTick = $ticks[$totalTicks - 1] ?? [];
$prevTick = $ticks[$totalTicks - 2] ?? [];

$currentPrice = (float)($lastTick['quote'] ?? 0);
$previousPrice = (float)($prevTick['quote'] ?? $currentPrice);

$delta = $currentPrice - $previousPrice;
$direction = 'FLAT';

if ($delta > 0) {
    $direction = 'UP';
} elseif ($delta < 0) {
    $direction = 'DOWN';
}

$lastTime = $lastTick['time'] ?? time();
$collectorDelay = time() - $lastTime;

if ($collectorDelay > 60) {
    $status = 'OFFLINE';
} elseif ($collectorDelay > 10) {
    $status = 'DELAY';
} else {
    $status = 'ONLINE';
}

if ($collectorDelay <= 1) {
    $velocity = 'FAST';
} elseif ($collectorDelay > 3) {
    $velocity = 'SLOW';
} else {
    $velocity = 'NORMAL';
}

$prices = [];
foreach (array_slice($ticks, -30) as $tick) {
    $prices[] = (float)($tick['quote'] ?? 0);
}

// Global helper function dideklarasikan di awal agar aman dipanggil kapan saja
function researchCard($title, $data) {
    $samples = $data['samples'] ?? 0;
    $wr      = $data['wr'] ?? 0;
    $trust   = $data['trust'] ?? 0;
    $updated = $data['updated'] ?? '-';

    if (is_numeric($updated)) {
        $updated = date('H:i:s', $updated);
    }

    echo '
    <div class="item">
        <div class="label">'.htmlspecialchars($title).'</div>
        <div class="value">'.$trust.'%</div>
        <div style="margin-top:8px;font-size:12px;opacity:.8;">
            WR : '.$wr.'%<br>
            Samples : '.number_format($samples).'<br>
            Updated : '.htmlspecialchars($updated).'
        </div>
    </div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AARM RC5 - Collector</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #081018; color: #fff; padding: 30px; }
        .wrapper { max-width: 1300px; margin: auto; }
        .card { background: #111d2c; border: 1px solid #22344b; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .title { font-size: 22px; font-weight: bold; margin-bottom: 20px; border-left: 4px solid #00d2ff; padding-left: 10px; }
        .price { font-size: 48px; font-weight: bold; margin-bottom: 8px; }
        .up { color: #00d26a; }
        .down { color: #ff5050; }
        .flat { color: #d0d0d0; }
        .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 15px; }
        .item { background: #0d1724; padding: 12px; border-radius: 8px; border: 1px solid #1a293b; }
        .label { font-size: 12px; opacity: .7; text-transform: uppercase; letter-spacing: 0.5px; }
        .value { font-size: 20px; margin-top: 6px; font-weight: bold; }
        canvas { margin-top: 20px; width: 100%; height: 90px; background: #071018; border-radius: 8px; border: 1px solid #1a293b; }
        .event { margin-top: 15px; padding: 12px; background: #0d1724; border-radius: 8px; border-left: 3px solid #ffd54f; font-size: 14px; }
        
        /* 2 Column Main Grid Layout */
        .analytics-main-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        @media (max-width: 950px) {
            .analytics-main-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 style="font-size: 24px;">AARM RC5</h1>
                <small style="font-size: 14px; opacity: 0.6;">Collector Engine Active Platform</small>
            </div>
            <div style="text-align: right;">
                <div id="price" class="price <?= strtolower($direction) ?>"><?= number_format($currentPrice, 5) ?></div>
                <div id="delta" class="<?= strtolower($direction) ?>"><?= ($delta >= 0 ? '+' : '') . number_format($delta, 5) ?></div>
            </div>
        </div>
        <div class="grid">
            <div class="item"><div class="label">Direction</div><div class="value" id="direction"><?= $direction ?></div></div>
            <div class="item"><div class="label">Total Tick</div><div class="value" id="tick"><?= number_format($totalTicks) ?></div></div>
            <div class="item"><div class="label">Collector Delay</div><div class="value" id="delay"><?= $collectorDelay ?> sec</div></div>
            <div class="item"><div class="label">Velocity</div><div class="value" id="velocity"><?= $velocity ?></div></div>
            <div class="item"><div class="label">Collector Status</div><div class="value" id="status"><?= $status ?></div></div>
            <div class="item"><div class="label">Last Update</div><div class="value" id="last"><?= date('H:i:s', $lastTime) ?></div></div>
        </div>
    </div>

    <div class="analytics-main-grid">
        
        <div class="grid-left-panel">
            
            <?php
            $brain = loadJson('brain_consensus_v4.json');
            $brainDecision   = $brain['decision']   ?? 'WAIT';
            $brainConfidence = $brain['confidence'] ?? 0;
            $brainRisk       = $brain['risk']       ?? 'WAITING DATA';
            $brainConsensus  = $brain['consensus']  ?? '0/0';
            $brainFitness    = $brain['fitness']    ?? 0;
            $brainDNA        = $brain['dna']        ?? '-';
            $brainReason     = $brain['reason']     ?? 'Waiting research...';

            if (is_array($brainReason)) {
                $brainReason = implode("<br>", array_map('htmlspecialchars', $brainReason));
            } else {
                $brainReason = htmlspecialchars((string)$brainReason);
            }
            $brainUpdated = $brain['updated'] ?? '-';
            if (is_numeric($brainUpdated)) {
                $brainUpdated = date('H:i:s', $brainUpdated);
            }

            switch (strtoupper($brainDecision)) {
                case 'CALL': $decisionColor = "#00d26a"; break;
                case 'PUT':  $decisionColor = "#ff5050"; break;
                default:     $decisionColor = "#ffd54f"; break;
            }
            ?>
            <div class="card">
                <div class="title">Brain Engine</div>
                <div class="grid">
                    <div class="item"><div class="label">Decision</div><div class="value" style="color:<?= $decisionColor ?>"><?= strtoupper($brainDecision) ?></div></div>
                    <div class="item"><div class="label">Confidence</div><div class="value"><?= $brainConfidence ?>%</div></div>
                    <div class="item"><div class="label">Consensus</div><div class="value"><?= $brainConsensus ?></div></div>
                    <div class="item"><div class="label">Risk</div><div class="value"><?= strtoupper($brainRisk) ?></div></div>
                    <div class="item"><div class="label">Fitness</div><div class="value"><?= $brainFitness ?></div></div>
                    <div class="item"><div class="label">DNA</div><div class="value" style="color:#00d2ff; font-size:15px; word-break:break-all;"><?= htmlspecialchars($brainDNA) ?></div></div>
                </div>
                <div class="event">
                    <b>Brain Reason</b><br><br><?= $brainReason ?><br><br>
                    <small style="opacity: 0.6;">Updated: <?= htmlspecialchars($brainUpdated) ?></small>
                </div>
            </div>

            <?php
            $reason = [
                'Trend'      => $brain['trend_status']      ?? 'WAITING DATA',
                'Pattern'    => $brain['pattern_status']    ?? 'WAITING DATA',
                'Digit'      => $brain['digit_status']      ?? 'WAITING DATA',
                'Indicator'  => $brain['indicator_status']  ?? 'WAITING DATA',
                'Hybrid'     => $brain['hybrid_status']     ?? 'WAITING DATA',
                'Knowledge'  => $brain['knowledge_status']  ?? 'WAITING DATA',
            ];

            $reasonPass = 0;
            foreach($reason as $v){
                if(strtoupper($v) == 'PASS') $reasonPass++;
            }

            function reasonColor($v){
                switch(strtoupper($v)){
                    case 'PASS': return '#00d26a';
                    case 'FAIL': return '#ff5050';
                    case 'WARNING': return '#ffd54f';
                    default: return '#7f8fa6';
                }
            }
            ?>
            <div class="card">
                <div class="title">Reason Engine</div>
                <div class="grid">
                    <?php foreach($reason as $name => $state): ?>
                    <div class="item">
                        <div class="label"><?= $name ?></div>
                        <div class="value" style="color:<?= reasonColor($state) ?>;"><?= strtoupper($state) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="event">
                    <b>Decision Quality</b><br>
                    Consensus : <b><?= $reasonPass ?> / <?= count($reason) ?></b><br><br>
                    <?php
                    if($reasonPass >= 5){
                        echo "<span style='color:#00d26a; font-weight:bold;'>🟢 APPROVED</span>";
                    } elseif($reasonPass >= 3){
                        echo "<span style='color:#ffd54f; font-weight:bold;'>🟡 WAITING CONFIRMATION</span>";
                    } else {
                        echo "<span style='color:#ff5050; font-weight:bold;'>🔴 REJECTED</span>";
                    }
                    ?>
                </div>
            </div>

            <?php
            $signals = loadJson('signals.json');
            $currentSignal = [];
            if(is_array($signals) && count($signals) > 0){
                $currentSignal = end($signals); 
            }

            $signalDirection  = strtoupper($currentSignal['signal'] ?? 'WAIT');
            $signalPattern    = $currentSignal['pattern'] ?? '-';
            $signalConfidence = $currentSignal['confidence'] ?? 0;
            $signalTick       = $currentSignal['tick'] ?? 0;
            $signalEntry      = $currentSignal['entry_price'] ?? '-';
            $signalCurrent    = $currentPrice;
            $signalState      = strtoupper($currentSignal['status'] ?? 'WAIT');

            $signalAge = '-';
            if(isset($currentSignal['time'])){
                $signalAge = time() - $currentSignal['time'];
            }

            $signalColor = '#ffd54f';
            switch($signalDirection){
                case 'CALL': $signalColor = '#00d26a'; break;
                case 'PUT':  $signalColor = '#ff5050'; break;
            }
            ?>
            <div class="card">
                <div class="title">Signal Engine</div>
                <div class="grid">
                    <div class="item"><div class="label">Signal</div><div class="value" style="color:<?= $signalColor ?>"><?= $signalDirection ?></div></div>
                    <div class="item"><div class="label">Pattern</div><div class="value" style="font-size:15px;"><?= htmlspecialchars($signalPattern) ?></div></div>
                    <div class="item"><div class="label">Confidence</div><div class="value"><?= $signalConfidence ?>%</div></div>
                    <div class="item"><div class="label">Target Tick</div><div class="value"><?= $signalTick ?></div></div>
                    <div class="item"><div class="label">Entry Price</div><div class="value"><?= $signalEntry ?></div></div>
                    <div class="item"><div class="label">Signal Age</div><div class="value"><?= $signalAge ?> sec</div></div>
                    <div class="item"><div class="label">Status State</div><div class="value"><?= $signalState ?></div></div>
                    <div class="item"><div class="label">Current Price</div><div class="value"><?= number_format($signalCurrent, 5) ?></div></div>
                </div>
            </div>

            <?php
            $feedbackSummary = loadJson('feedback_summary.json');
            $totalWin      = (int)($feedbackSummary['win'] ?? 0);
            $totalLoss     = (int)($feedbackSummary['loss'] ?? 0);
            $totalFeedback = $totalWin + $totalLoss;
            $wr = 0;

            if($totalFeedback > 0){
                $wr = round(($totalWin / $totalFeedback) * 100, 2);
            }
            $lastFeedback = $feedbackSummary['last_feedback'] ?? '-';
            if(is_numeric($lastFeedback)){
                $lastFeedback = date('H:i:s', $lastFeedback);
            }
            ?>
            <div class="card">
                <div class="title">Feedback Engine</div>
                <div class="grid">
                    <div class="item"><div class="label">WIN</div><div class="value" style="color:#00d26a;"><?= number_format($totalWin) ?></div></div>
                    <div class="item"><div class="label">LOSS</div><div class="value" style="color:#ff5050;"><?= number_format($totalLoss) ?></div></div>
                    <div class="item"><div class="label">Feedback</div><div class="value"><?= number_format($totalFeedback) ?></div></div>
                    <div class="item"><div class="label">Win Rate</div><div class="value"><?= $wr ?>%</div></div>
                </div>
                <div class="event">
                    <b>Latest Feedback</b><br><br><?= htmlspecialchars((string)$lastFeedback) ?>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:15px;">
                    <button id="btnWin" style="padding:14px; border:0; border-radius:8px; background:#00d26a; color:#fff; font-weight:bold; cursor:pointer;">✅ WIN</button>
                    <button id="btnLoss" style="padding:14px; border:0; border-radius:8px; background:#ff5050; color:#fff; font-weight:bold; cursor:pointer;">❌ LOSS</button>
                </div>
                <div id="feedbackStatus" style="margin-top:10px; font-size:13px; opacity:.8; text-align:center;">Waiting feedback...</div>
                
                <canvas id="spark"></canvas>
            </div>

            <?php
            $paperState = loadJson('paper_trade_state.json');
            $tradeState      = strtoupper($paperState['state'] ?? 'WAITING');
            $tradeDirection  = strtoupper($paperState['direction'] ?? '-');
            $tradeConfidence = (int)($paperState['confidence'] ?? 0);
            $tradeEntry      = $paperState['entry'] ?? '-';
            $tradeCurrent    = number_format($currentPrice, 5);
            $tradeTick       = (int)($paperState['tick'] ?? 0);
            $tradeTarget     = (int)($paperState['target_tick'] ?? 0);
            $tradeFloating   = strtoupper($paperState['floating'] ?? '-');

            $tradeColor = "#ffd54f";
            switch($tradeDirection){
                case 'CALL': $tradeColor = "#00d26a"; break;
                case 'PUT':  $tradeColor = "#ff5050"; break;
            }

            $progress = 0;
            if($tradeTarget > 0){
                $progress = round(($tradeTick / $tradeTarget) * 100);
            }
            ?>
            <div class="card">
                <div class="title">Paper Trade Engine</div>
                <div class="grid">
                    <div class="item"><div class="label">Trade State</div><div class="value" style="color:<?= $tradeColor ?>"><?= $tradeState ?></div></div>
                    <div class="item"><div class="label">Direction</div><div class="value" style="color:<?= $tradeColor ?>"><?= $tradeDirection ?></div></div>
                    <div class="item"><div class="label">Confidence</div><div class="value"><?= $tradeConfidence ?>%</div></div>
                    <div class="item"><div class="label">Entry Price</div><div class="value"><?= $tradeEntry ?></div></div>
                    <div class="item"><div class="label">Current Price</div><div class="value"><?= $tradeCurrent ?></div></div>
                    <div class="item"><div class="label">Floating</div><div class="value"><?= $tradeFloating ?></div></div>
                    <div class="item"><div class="label">Tick Progress</div><div class="value"><?= $tradeTick ?> / <?= $tradeTarget ?></div></div>
                    <div class="item"><div class="label">Progress</div><div class="value"><?= $progress ?>%</div></div>
                </div>
                <div style="margin-top:20px; background:#1d2c40; height:18px; border-radius:20px; overflow:hidden;">
                    <div style="width:<?= $progress ?>%; height:100%; background:#00d26a; transition:.3s;"></div>
                </div>
                <div class="event">
                    <b>Paper Trade Status</b><br><br>
                    State : <b><?= $tradeState ?></b> | Floating : <b><?= $tradeFloating ?></b> | Tick : <b><?= $tradeTick ?> / <?= $tradeTarget ?></b>
                </div>
            </div>

        </div>

        <div class="grid-right-panel">
            
            <?php
            $trendResearch     = loadJson('trend_research.json');
            $patternResearch   = loadJson('pattern_research.json');
            $digitResearch     = loadJson('digit_research.json');
            $indicatorResearch = loadJson('indicator_research.json');
            $hybridResearch    = loadJson('hybrid_research.json');
            ?>
            <div class="card">
                <div class="title">Research Engine</div>
                <div class="grid">
                    <?php
                    researchCard('Trend', $trendResearch);
                    researchCard('Pattern', $patternResearch);
                    researchCard('Digit', $digitResearch);
                    researchCard('Indicator', $indicatorResearch);
                    researchCard('Hybrid', $hybridResearch);
                    ?>
                    <div class="item">
                        <div class="label">Research Status</div>
                        <div class="value" style="color:#00d26a;">ONLINE</div>
                        <div style="margin-top:8px;font-size:12px;opacity:.8;">Waiting adaptive data...</div>
                    </div>
                </div>
                <div class="event">
                    <b>Latest Event</b><br><br>
                    Tick received at <span id="eventTime"><?= date('H:i:s', $lastTime) ?></span>
                </div>
            </div>

            <?php
            $knowledgeBest = loadJson('knowledge_best.json');
            $knowledgeOpt  = loadJson('knowledge_optimized.json');

            $bestPattern = '-';
            $bestWR      = 0;
            $bestTrust   = 0;
            $bestSamples = 0;
            $knowledgeUpdated = '-';

            if (!empty($knowledgeBest)) {
                $first = current($knowledgeBest);
                if (is_array($first)) {
                    $bestPattern = $first['pattern'] ?? (string)array_key_first($knowledgeBest);
                    $bestWR      = $first['wr'] ?? 0;
                    $bestTrust   = $first['trust'] ?? 0;
                    $bestSamples = $first['samples'] ?? 0;
                    $knowledgeUpdated = $first['updated'] ?? '-';
                }
            }
            $totalKnowledge = count($knowledgeBest);
            $totalOptimizer = count($knowledgeOpt);
            ?>
            <div class="card">
                <div class="title">Knowledge Engine</div>
                <div class="grid">
                    <div class="item"><div class="label">Best Pattern</div><div class="value" style="color:#00d2ff; font-size:15px;"><?= htmlspecialchars($bestPattern) ?></div></div>
                    <div class="item"><div class="label">Winning Rate</div><div class="value"><?= $bestWR ?>%</div></div>
                    <div class="item"><div class="label">Trust</div><div class="value"><?= $bestTrust ?></div></div>
                    <div class="item"><div class="label">Samples</div><div class="value"><?= number_format($bestSamples) ?></div></div>
                    <div class="item"><div class="label">Knowledge Memory</div><div class="value"><?= number_format($totalKnowledge) ?></div></div>
                    <div class="item"><div class="label">Optimizer Memory</div><div class="value"><?= number_format($totalOptimizer) ?></div></div>
                </div>

                <table style="width:100%; margin-top:20px; border-collapse:collapse; font-size:13px;">
                    <thead>
                        <tr style="background:#132235;">
                            <th align="left" style="padding:8px;">Pattern</th>
                            <th>WR</th>
                            <th>Trust</th>
                            <th>Samples</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($knowledgeBest)): $limit = 0; foreach ($knowledgeBest as $row): if ($limit++ >= 5) break; ?>
                        <tr style="border-bottom: 1px solid #132235;">
                            <td style="padding:8px;"><?= htmlspecialchars($row['pattern'] ?? '-') ?></td>
                            <td align="center"><?= $row['wr'] ?? 0 ?>%</td>
                            <td align="center"><?= $row['trust'] ?? 0 ?></td>
                            <td align="center"><?= $row['samples'] ?? 0 ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>

                <div class="event" style="margin-top:15px;">
                    <b>Knowledge Summary</b><br><br>
                    Best Pattern: <b><?= htmlspecialchars($bestPattern) ?></b><br>
                    Memory: <?= number_format($totalKnowledge) ?> records<br>
                    Optimizer: <?= number_format($totalOptimizer) ?> records<br>
                    Updated: <?= is_numeric($knowledgeUpdated) ? date('H:i:s', $knowledgeUpdated) : htmlspecialchars($knowledgeUpdated) ?>
                </div>
            </div>

            <?php
            $experience = loadJson('experience_summary.json');
            $totalExperience = (int)($experience['total'] ?? 0);
            $expBestPattern = $experience['best_pattern'] ?? '-';
            $expBestWR = $experience['best_wr'] ?? 0;
            $expBestTrust = $experience['best_trust'] ?? 0;
            $lastLearn = $experience['last_learning'] ?? '-';

            if(is_numeric($lastLearn)){
                $lastLearn = date('H:i:s', $lastLearn);
            }
            $cycles = (int)($experience['cycles'] ?? 0);
            ?>
            <div class="card">
                <div class="title">Experience Engine</div>
                <div class="grid">
                    <div class="item"><div class="label">Experience Memory</div><div class="value"><?= number_format($totalExperience) ?></div></div>
                    <div class="item"><div class="label">Learning Cycle</div><div class="value"><?= number_format($cycles) ?></div></div>
                    <div class="item"><div class="label">Best Pattern</div><div class="value" style="color:#00d2ff; font-size:15px;"><?= htmlspecialchars($expBestPattern) ?></div></div>
                    <div class="item"><div class="label">Best WR</div><div class="value"><?= $expBestWR ?>%</div></div>
                    <div class="item"><div class="label">Best Trust</div><div class="value"><?= $expBestTrust ?></div></div>
                    <div class="item"><div class="label">Last Learning</div><div class="value" style="font-size:15px;"><?= htmlspecialchars((string)$lastLearn) ?></div></div>
                </div>
                <div class="event">
                    <b>Experience Summary</b><br><br>
                    Memory : <b><?= number_format($totalExperience) ?></b> records<br>
                    Best Pattern : <b><?= htmlspecialchars($expBestPattern) ?></b><br>
                    Learning Cycle : <b><?= number_format($cycles) ?></b><br>
                    Last Update : <b><?= htmlspecialchars((string)$lastLearn) ?></b>
                </div>
            </div>

            <?php
            $health = [];
            $health['Collector']  = file_exists(STORAGE.'historical_ticks.json');
            $health['Research']   = file_exists(STORAGE.'trend_research.json') || file_exists(STORAGE.'pattern_research.json');
            $health['Knowledge']  = file_exists(STORAGE.'knowledge_best.json');
            $health['Brain']      = file_exists(STORAGE.'brain_consensus_v4.json');
            $health['Signal']     = file_exists(STORAGE.'signals.json');
            $health['Paper']      = file_exists(STORAGE.'paper_trade_state.json');
            $health['Feedback']   = file_exists(STORAGE.'feedback_summary.json');
            $health['Experience'] = file_exists(STORAGE.'experience_summary.json');
            $health['Telegram']   = file_exists(ROOT.'/telegram/send_signal.php');

            $total = count($health);
            $alive = 0;
            foreach($health as $v){
                if($v) $alive++;
            }

            $score = round(($alive / $total) * 100);
            $status = 'NOT READY';
            $statusColor = '#ff5050';

            if($score >= 90){
                $status = 'READY';
                $statusColor = '#00d26a';
            } elseif($score >= 70){
                $status = 'PARTIAL';
                $statusColor = '#ffd54f';
            }
            ?>
            <div class="card">
                <div class="title">RC5 Health Validator</div>
                <div class="grid">
                    <?php foreach($health as $name => $ok): ?>
                    <div class="item">
                        <div class="label"><?= $name ?></div>
                        <div class="value" style="color:<?= $ok ? '#00d26a' : '#ff5050' ?>; font-size:16px;">
                            <?= $ok ? 'ONLINE' : 'OFFLINE' ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="event">
                    <b>Pipeline Status</b><br><br>
                    Health Score: <b><?= $score ?>%</b><br><br>
                    Pipeline: <b style="color:<?= $statusColor ?>"><?= $status ?></b><br><br>
                    <?php
                    if($status == 'READY'){
                        echo "🟢 RC5 READY FOR LIVE VALIDATION";
                    } elseif($status == 'PARTIAL'){
                        echo "🟡 Some engines still waiting data";
                    } else {
                        echo "🔴 Pipeline incomplete";
                    }
                    ?>
                </div>
                <div style="text-align:center; padding:20px; font-size:22px; font-weight:bold; color:<?= $statusColor ?>; border: 1px dashed <?= $statusColor ?>; border-radius: 8px; margin-top: 15px;">
                    AARM RC5 <?= $status ?>
                </div>
            </div>

        </div>
    </div> </div>

<script>
// Logic feedback sistem manual
async function sendFeedback(result) {
    const status = document.getElementById("feedbackStatus");
    status.innerHTML = "Saving feedback...";
    try {
        const r = await fetch("../feedback/manual_feedback.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "result=" + result
        });
        const txt = await r.text();
        status.innerHTML = "✅ " + txt;
    } catch(e) {
        status.innerHTML = "❌ Failed";
    }
}

document.getElementById("btnWin").onclick = () => sendFeedback("WIN");
document.getElementById("btnLoss").onclick = () => sendFeedback("LOSS");

// Logic Chart Engine Sparkline
let sparkData = <?= json_encode($prices) ?>;

function drawSpark() {
    const c = document.getElementById('spark');
    if (!c) return;
    const ctx = c.getContext('2d');
    c.width = c.offsetWidth;
    c.height = 90;
    ctx.clearRect(0, 0, c.width, c.height);

    if (sparkData.length < 2) return;

    const min = Math.min(...sparkData);
    const max = Math.max(...sparkData);

    ctx.beginPath();
    sparkData.forEach((v, i) => {
        const x = i * (c.width / (sparkData.length - 1));
        const y = (max === min) ? 45 : 80 - ((v - min) / (max - min)) * 70;
        if (i === 0) ctx.moveTo(x, y);
        else ctx.lineTo(x, y);
    });

    ctx.strokeStyle = "#00d2ff";
    ctx.lineWidth = 2;
    ctx.stroke();
}

// Inisialisasi Canvas setelah DOM ter-render
drawSpark();

// Mekanisme refresh yang aman & non-blocking
let isRefreshing = false;
async function refreshCollector() {
    if (isRefreshing) return; 
    isRefreshing = true;

    try {
        await fetch("../engines/collector.php?_=" + Date.now());
        location.reload();
    } catch(e) {
        console.error("Collector fetch failed:", e);
        isRefreshing = false; 
    }
}

setInterval(refreshCollector, 2000); 
</script>
</body>
</html>