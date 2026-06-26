<?php

error_reporting(E_ALL);

$knowledgeFile =
    dirname(__DIR__).'/storage/knowledge_status.json';

$logFile =
    dirname(__DIR__).'/storage/knowledge_evolution_log.json';

$knowledge = [];

$logs = [];

if(file_exists($knowledgeFile)){

    $knowledge =
        json_decode(
            file_get_contents(
                $knowledgeFile
            ),
            true
        ) ?: [];

}

if(file_exists($logFile)){

    $logs =
        json_decode(
            file_get_contents(
                $logFile
            ),
            true
        ) ?: [];

}

/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$summary = [

    'ACTIVE' => 0,
    'DECAYING' => 0,
    'DEAD' => 0,
    'GRAVEYARD' => 0

];

foreach($knowledge as $item){

    $status =
        $item['status']
        ?? 'UNKNOWN';

    if(
        isset(
            $summary[$status]
        )
    ){
        $summary[$status]++;
    }

}

/*
|--------------------------------------------------------------------------
| Promotions / Demotions
|--------------------------------------------------------------------------
*/

$promotions = 0;
$demotions  = 0;

foreach($logs as $row){

    $old =
        $row['old_status']
        ?? '';

    $new =
        $row['new_status']
        ?? '';

$rank = [

    'ACTIVE'     => 5,
    'UNPROVEN'   => 4,
    'DECAYING'   => 3,
    'DEAD'       => 2,
    'GRAVEYARD'  => 1

];

    if(
        isset($rank[$old]) &&
        isset($rank[$new])
    ){

        if(
            $rank[$new]
            >
            $rank[$old]
        ){
            $promotions++;
        }

        if(
            $rank[$new]
            <
            $rank[$old]
        ){
            $demotions++;
        }

    }

}

$totalKnowledge =
    count($knowledge);

$totalChanges =
    count($logs);

?>
<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">

<title>
ANTARTIKA Evolution Dashboard
</title>

<style>

body{

    background:#0f172a;
    color:#f8fafc;
    font-family:Arial;
    margin:20px;

}

.card{

    background:#1e293b;
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;

}

.grid{

    display:grid;
    grid-template-columns:
        repeat(4,1fr);

    gap:15px;

}

.box{

    background:#111827;
    border-radius:10px;
    padding:20px;
    text-align:center;

}

.big{

    font-size:32px;
    font-weight:bold;

}

.green{

    color:#22c55e;

}

.yellow{

    color:#facc15;

}

.red{

    color:#ef4444;

}

.gray{

    color:#9ca3af;

}

.blue{

    color:#60a5fa;

}

table{

    width:100%;
    border-collapse:collapse;

}

table th,
table td{

    border:1px solid #334155;
    padding:10px;

}

table th{

    background:#111827;

}

</style>

</head>
<body>

<h1>
🧠 ANTARTIKA Evolution Dashboard
</h1>

<div class="card">

<h2>
Knowledge Summary
</h2>

<div class="grid">

<div class="box">
<div class="big green">
<?= $summary['ACTIVE'] ?>
</div>
ACTIVE
</div>

<div class="box">
<div class="big yellow">
<?= $summary['DECAYING'] ?>
</div>
DECAYING
</div>

<div class="box">
<div class="big red">
<?= $summary['DEAD'] ?>
</div>
DEAD
</div>

<div class="box">
<div class="big gray">
<?= $summary['GRAVEYARD'] ?>
</div>
GRAVEYARD
</div>

</div>

</div>

<div class="card">

<h2>
Evolution Statistics
</h2>

<p>
Total Knowledge :
<b>
<?= $totalKnowledge ?>
</b>
</p>

<p>
Evolution Changes :
<b>
<?= $totalChanges ?>
</b>
</p>

<p>
Promotions :
<b class="green">
<?= $promotions ?>
</b>
</p>

<p>
Demotions :
<b class="red">
<?= $demotions ?>
</b>
</p>

</div>

<div class="card">

<h2>
Recent Evolution
</h2>

<table>

<tr>

<th>Time</th>
<th>Key</th>
<th>Score</th>
<th>Old</th>
<th>New</th>

</tr>

<?php

$recent =
    array_reverse(
        $logs
    );

$recent =
    array_slice(
        $recent,
        0,
        50
    );

foreach($recent as $row):

?>

<tr>

<td>
<?= htmlspecialchars(
    $row['time']
    ?? ''
) ?>
</td>

<td>
<?= htmlspecialchars(
    $row['key']
    ?? ''
) ?>
</td>

<td>
<?= htmlspecialchars(
    $row['score']
    ?? ''
) ?>
</td>

<td>
<?= htmlspecialchars(
    $row['old_status']
    ?? ''
) ?>
</td>

<td>
<?= htmlspecialchars(
    $row['new_status']
    ?? ''
) ?>
</td>

</tr>

<?php endforeach; ?>

</table>

</div>

<div class="card">

<h2>
System Health
</h2>

<p>

Knowledge Base :
<b>
<?= $totalKnowledge ?>
</b>

</p>

<p>

Evolution Log :
<b>
<?= $totalChanges ?>
</b>

</p>

<p>

Learning Status :
<b class="blue">
ACTIVE
</b>

</p>

</div>

</body>
</html>