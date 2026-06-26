<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA ADAPTIVE RESEARCH MACHINE
|--------------------------------------------------------------------------
| RESEARCH ORCHESTRATOR RC5
|--------------------------------------------------------------------------
| Purpose:
| - Mengatur prioritas penelitian
| - Menjalankan seluruh research node
| - Menjadi pusat koordinasi AARM
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);
date_default_timezone_set('Asia/Jakarta');

$root = dirname(__DIR__);

$storage = $root . '/storage';

/*
|--------------------------------------------------------------------------
| Research Nodes
|--------------------------------------------------------------------------
*/

$nodes = [

    [
        'name' => 'PATTERN',
        'file' => __DIR__ . '/context_research_engine_v2.php',
        'priority' => 100,
        'enabled' => true
    ],

    [
        'name' => 'EXPERIENCE',
        'file' => __DIR__ . '/experience_engine.php',
        'priority' => 95,
        'enabled' => true
    ],

    [
        'name' => 'TRUST',
        'file' => __DIR__ . '/trust_engine.php',
        'priority' => 90,
        'enabled' => true
    ],

[
    'name' => 'TREND_RESEARCH',
    'file' => __DIR__ . '/trend_research_engine.php',
    'priority' => 85,
    'enabled' => true
],

[
    'name' => 'DIGIT_RESEARCH',
    'file' => __DIR__ . '/digit_research_engine.php',
    'priority' => 84,
    'enabled' => true
],

[
    'name' => 'LAST2_RESEARCH',
    'file' => __DIR__ . '/last2_research_engine.php',
    'priority' => 83,
    'enabled' => true
],

];

/*
|--------------------------------------------------------------------------
| Future Research Nodes (RC5)
|--------------------------------------------------------------------------
*/

$futureNodes = [

    'TREND',

    'MARKET',

    'SESSION',

    'LAST2',

    'DIGIT',

    'HYBRID',

    'EXECUTION'

];

/*
|--------------------------------------------------------------------------
| Sort Priority
|--------------------------------------------------------------------------
*/

usort($nodes,function($a,$b){

    return $b['priority'] <=> $a['priority'];

});

/*
|--------------------------------------------------------------------------
| Execute
|--------------------------------------------------------------------------
*/

$results = [];

foreach($nodes as $node){

    if(!$node['enabled']){

        continue;

    }

    $start = microtime(true);

    $status = 'OK';

    if(file_exists($node['file'])){

        try{

            include $node['file'];

        }catch(Throwable $e){

            $status='ERROR';

        }

    }else{

        $status='NOT_FOUND';

    }

    $results[]=[

        'node'=>$node['name'],

        'priority'=>$node['priority'],

        'status'=>$status,

        'runtime_ms'=>round((microtime(true)-$start)*1000,2)

    ];

}

/*
|--------------------------------------------------------------------------
| Save Orchestrator Status
|--------------------------------------------------------------------------
*/

$output=[

    'time'=>date('Y-m-d H:i:s'),

    'version'=>'RC5',

    'mode'=>'RESEARCH',

    'market'=>'R_25',

    'status'=>'RUNNING',

    'completed_nodes'=>count($results),

    'future_nodes'=>$futureNodes,

    'results'=>$results

];

file_put_contents(

    $storage.'/research_orchestrator.json',

    json_encode(

        $output,

        JSON_PRETTY_PRINT

    )

);

/*
|--------------------------------------------------------------------------
| CLI Output
|--------------------------------------------------------------------------
*/

echo PHP_EOL;

echo "====================================".PHP_EOL;

echo " ANTARTIKA RESEARCH ORCHESTRATOR RC5".PHP_EOL;

echo "====================================".PHP_EOL;

foreach($results as $r){

    echo str_pad($r['node'],15);

    echo str_pad($r['status'],12);

    echo $r['runtime_ms']." ms";

    echo PHP_EOL;

}

echo "====================================".PHP_EOL;

echo "Research Cycle Completed".PHP_EOL;

echo "====================================".PHP_EOL;