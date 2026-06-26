<?php

/*
|--------------------------------------------------------------------------
| ANTARTIKA ADAPTIVE RESEARCH MACHINE
|--------------------------------------------------------------------------
| GENE BUILDER
|--------------------------------------------------------------------------
| FINAL ARCHITECTURE
|--------------------------------------------------------------------------
| Scientist -> Genes -> DNA
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

$ROOT = dirname(__DIR__);
$STORAGE = $ROOT . '/storage';

$OUTPUT = $STORAGE . '/genes.json';

/*
|--------------------------------------------------------------------------
| Scientist Sources
|--------------------------------------------------------------------------
*/

$scientists = [

    'pattern' => $STORAGE.'/pattern_research.json',

    'trend'   => $STORAGE.'/trend_research.json',

    'market'  => $STORAGE.'/market_research.json',

    'session' => $STORAGE.'/session_research.json',

    'digit'   => $STORAGE.'/digit_research.json',

    'last2'   => $STORAGE.'/last2_research.json',

    'hybrid'  => $STORAGE.'/hybrid_research.json'

];

$genes=[];

/*
|--------------------------------------------------------------------------
| Build Genes
|--------------------------------------------------------------------------
*/

foreach($scientists as $type=>$file){

    if(!file_exists($file)){

        continue;

    }

    $rows=json_decode(

        file_get_contents($file),

        true

    ) ?: [];

    foreach($rows as $row){

        $id = strtoupper($type)."_".substr(

            sha1(

                json_encode($row)

            ),

            0,

            12

        );

        $genes[$id]=[

            'gene_id'=>$id,

            'scientist'=>$type,

            'signature'=>$row,

            'fitness'=>$row['trust'] ?? 50,

            'confidence'=>$row['confidence'] ?? 50,

            'samples'=>$row['samples'] ?? 0,

            'survival'=>$row['survival'] ?? 'DISCOVERED',

            'knowledge'=>$row['knowledge'] ?? 'LEARNING',

            'created_at'=>date('Y-m-d H:i:s'),

            'updated_at'=>date('Y-m-d H:i:s')

        ];

    }

}

/*
|--------------------------------------------------------------------------
| Rank
|--------------------------------------------------------------------------
*/

uasort(

    $genes,

    function($a,$b){

        return

        ($b['fitness'] ?? 0)

        <=>

        ($a['fitness'] ?? 0);

    }

);

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

$stats=[

'total_genes'=>count($genes),

'elite'=>0,

'survivor'=>0,

'candidate'=>0,

'learning'=>0

];

foreach($genes as $g){

    switch($g['survival']){

        case 'ELITE':

            $stats['elite']++;

        break;

        case 'SURVIVOR':

            $stats['survivor']++;

        break;

        case 'CANDIDATE':

            $stats['candidate']++;

        break;

        default:

            $stats['learning']++;

        break;

    }

}

/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

$output=[

'version'=>'AARM FINAL',

'generated_at'=>date('Y-m-d H:i:s'),

'statistics'=>$stats,

'genes'=>array_values($genes)

];

file_put_contents(

    $OUTPUT,

    json_encode(

        $output,

        JSON_PRETTY_PRINT

    )

);

echo PHP_EOL;

echo "========================================".PHP_EOL;

echo " AARM GENE BUILDER".PHP_EOL;

echo "========================================".PHP_EOL;

echo "Genes : ".count($genes).PHP_EOL;

echo "Elite : ".$stats['elite'].PHP_EOL;

echo "Survivor : ".$stats['survivor'].PHP_EOL;

echo "Candidate : ".$stats['candidate'].PHP_EOL;

echo "Learning : ".$stats['learning'].PHP_EOL;

echo "========================================".PHP_EOL;

echo "GENE BUILD COMPLETE".PHP_EOL;