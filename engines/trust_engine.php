<?php

$expFile =
    dirname(__DIR__).'/storage/experience.json';

$trustFile =
    dirname(__DIR__).'/storage/trust.json';

$exp = json_decode(
    file_get_contents($expFile),
    true
) ?: [];

$trust = [];

foreach($exp as $pattern=>$row){

$baseTrust = $row['trust'] ?? 50;

$confidence = $row['confidence'] ?? 50;

$stability = $row['stability'] ?? 'LOW';

$survival = $row['survival'] ?? 'UNPROVEN';

$research = $row['research_score'] ?? 50;

$finalTrust = round(

    ($baseTrust * 0.40)

    +

    ($confidence * 0.30)

    +

    ($research * 0.30)

,2);

    if($survival=='ELITE'){

        $status='ELITE';

    }
    elseif($survival=='SURVIVOR'){

        $status='SURVIVOR';

    }
    elseif($finalTrust>=70){

        $status='GOOD';

    }
    elseif($finalTrust>=55){

        $status='WATCH';

    }
    else{

        $status='BAD';

    }

    $trust[$pattern]=[

        'trust'=>$finalTrust,

        'status'=>$status,

        'survival'=>$survival,

        'confidence'=>$confidence,

        'research_score'=>$research,

        'stability'=>$stability,

        'total'=>$row['total'] ?? 0,

        'updated_at'=>date('Y-m-d H:i:s')

    ];

}

file_put_contents(

    $trustFile,

    json_encode(
        $trust,
        JSON_PRETTY_PRINT
    )

);

echo "TRUST UPDATED";