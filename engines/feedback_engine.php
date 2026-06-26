<?php

error_reporting(E_ALL);

/*
|--------------------------------------------------------------------------
| AARM FINAL
|--------------------------------------------------------------------------
| FEEDBACK ENGINE
|--------------------------------------------------------------------------
| PART 1 / 2
|--------------------------------------------------------------------------
| Learn From Paper Trades
|--------------------------------------------------------------------------
*/

$paperFile =
    dirname(__DIR__).'/storage/paper_trades.json';

$summaryFile =
    dirname(__DIR__).'/storage/feedback_summary.json';

$feedbackKnowledgeFile =
    dirname(__DIR__).'/storage/feedback_knowledge.json';

if(!file_exists($paperFile)){
    exit('PAPER TRADE NOT FOUND');
}

$trades =
json_decode(

    file_get_contents(
        $paperFile
    ),

true

) ?: [];

if(empty($trades)){
    exit('NO PAPER TRADE');
}

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

$totalTrade = 0;

$totalWin = 0;

$totalLoss = 0;

$knowledge = [];

$dnaQuality = [];

$riskStats = [];

$durationStats = [];

$patternStats = [];

/*
|--------------------------------------------------------------------------
| Read Every Closed Trade
|--------------------------------------------------------------------------
*/

foreach($trades as $trade){

    if(

        !isset($trade['result'])

        ||

        $trade['result']===null

    ){

        continue;

    }

    $totalTrade++;

    $pattern =
        $trade['pattern']
        ??'UNKNOWN';

    $direction =
        $trade['direction']
        ??'WAIT';

    $duration =
        (int)(
            $trade['duration']
            ??3
        );

    $quality =
        $trade['dna_quality']
        ??'D';

    $risk =
        $trade['risk']
        ??'HIGH';

    $confidence =
        (float)(
            $trade['confidence']
            ??0
        );

    $fitness =
        (float)(
            $trade['fitness']
            ??0
        );

    $trust =
        (float)(
            $trade['trust']
            ??0
        );

    $result =
        strtoupper(
            $trade['result']
        );

    /*
    |--------------------------------------------------------------------------
    | Global
    |--------------------------------------------------------------------------
    */

    if($result=='WIN'){

        $totalWin++;

    }else{

        $totalLoss++;

    }

    /*
    |--------------------------------------------------------------------------
    | Pattern
    |--------------------------------------------------------------------------
    */

    if(!isset($patternStats[$pattern])){

        $patternStats[$pattern]=[

            'win'=>0,

            'loss'=>0,

            'total'=>0

        ];

    }

    $patternStats[$pattern]['total']++;

    if($result=='WIN'){

        $patternStats[$pattern]['win']++;

    }else{

        $patternStats[$pattern]['loss']++;

    }

    /*
    |--------------------------------------------------------------------------
    | Duration
    |--------------------------------------------------------------------------
    */

    if(!isset($durationStats[$duration])){

        $durationStats[$duration]=[

            'win'=>0,

            'loss'=>0,

            'total'=>0

        ];

    }

    $durationStats[$duration]['total']++;

    if($result=='WIN'){

        $durationStats[$duration]['win']++;

    }else{

        $durationStats[$duration]['loss']++;

    }

    /*
    |--------------------------------------------------------------------------
    | DNA Quality
    |--------------------------------------------------------------------------
    */

    if(!isset($dnaQuality[$quality])){

        $dnaQuality[$quality]=[

            'win'=>0,

            'loss'=>0,

            'total'=>0

        ];

    }

    $dnaQuality[$quality]['total']++;

    if($result=='WIN'){

        $dnaQuality[$quality]['win']++;

    }else{

        $dnaQuality[$quality]['loss']++;

    }

    /*
    |--------------------------------------------------------------------------
    | Risk
    |--------------------------------------------------------------------------
    */

    if(!isset($riskStats[$risk])){

        $riskStats[$risk]=[

            'win'=>0,

            'loss'=>0,

            'total'=>0

        ];

    }

    $riskStats[$risk]['total']++;

    if($result=='WIN'){

        $riskStats[$risk]['win']++;

    }else{

        $riskStats[$risk]['loss']++;

    }

}

/*
|--------------------------------------------------------------------------
| CONTINUE PART 2
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Calculate WR
|--------------------------------------------------------------------------
*/

$overallWR = 0;

if($totalTrade>0){

    $overallWR = round(

        ($totalWin/$totalTrade)*100,

        2

    );

}

/*
|--------------------------------------------------------------------------
| Normalize Statistics
|--------------------------------------------------------------------------
*/

foreach($patternStats as $pattern=>&$row){

    $row['wr']=round(

        ($row['win']/

        max(1,$row['total']))

        *100,

        2

    );

}

foreach($durationStats as $duration=>&$row){

    $row['wr']=round(

        ($row['win']/

        max(1,$row['total']))

        *100,

        2

    );

}

foreach($dnaQuality as $quality=>&$row){

    $row['wr']=round(

        ($row['win']/

        max(1,$row['total']))

        *100,

        2

    );

}

foreach($riskStats as $risk=>&$row){

    $row['wr']=round(

        ($row['win']/

        max(1,$row['total']))

        *100,

        2

    );

}

/*
|--------------------------------------------------------------------------
| Feedback Knowledge
|--------------------------------------------------------------------------
*/

$knowledge = [

    'generated_at' =>

        date('Y-m-d H:i:s'),

    'total_trade' =>

        $totalTrade,

    'win' =>

        $totalWin,

    'loss' =>

        $totalLoss,

    'overall_wr' =>

        $overallWR,

    'patterns' =>

        $patternStats,

    'duration' =>

        $durationStats,

    'dna_quality' =>

        $dnaQuality,

    'risk' =>

        $riskStats

];

/*
|--------------------------------------------------------------------------
| Save Knowledge
|--------------------------------------------------------------------------
*/

file_put_contents(

    $feedbackKnowledgeFile,

    json_encode(

        $knowledge,

        JSON_PRETTY_PRINT

    )

);

/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$summary = [

    'engine' =>

    'AARM FINAL',
		
	'knowledge_target' => 'feedback_knowledge.json',

    'generated_at' =>

        date('Y-m-d H:i:s'),

    'total_trade' =>

        $totalTrade,

    'win' =>

        $totalWin,

    'loss' =>

        $totalLoss,

    'wr' =>

        $overallWR,

    'best_pattern' =>

        array_key_first(

            array_filter(

                $patternStats,

                function($v){

                    return $v['wr']>=60;

                }

            )

        ),

    'best_duration' =>

        array_key_first(

            array_filter(

                $durationStats,

                function($v){

                    return $v['wr']>=60;

                }

            )

        )

];

file_put_contents(

    $summaryFile,

    json_encode(

        $summary,

        JSON_PRETTY_PRINT

    )

);

/*
|--------------------------------------------------------------------------
| Console
|--------------------------------------------------------------------------
*/

echo PHP_EOL;

echo "================================".PHP_EOL;

echo " AARM FEEDBACK ENGINE ".PHP_EOL;

echo "================================".PHP_EOL;

echo "Trades      : ".$totalTrade.PHP_EOL;

echo "WIN         : ".$totalWin.PHP_EOL;

echo "LOSS        : ".$totalLoss.PHP_EOL;

echo "WR          : ".$overallWR." %".PHP_EOL;

echo "Patterns    : ".count($patternStats).PHP_EOL;

echo "Duration    : ".count($durationStats).PHP_EOL;

echo "Risk Groups : ".count($riskStats).PHP_EOL;

echo "DNA Grade   : ".count($dnaQuality).PHP_EOL;

echo "Saved : feedback_knowledge.json".PHP_EOL;

echo "Saved       : feedback_summary.json".PHP_EOL;

echo "================================".PHP_EOL;

?>