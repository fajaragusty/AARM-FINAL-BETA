<?php

error_reporting(E_ALL);


/*
|--------------------------------------------------------------------------
| Files
|--------------------------------------------------------------------------
*/

$experienceFile =
    dirname(__DIR__).'/storage/experience_v2.json';

$adaptiveFile =
    dirname(__DIR__).'/storage/adaptive.json';
/*
|--------------------------------------------------------------------------
| Load Experience
|--------------------------------------------------------------------------
*/

$experience = [];

if(file_exists($experienceFile)){

    $experience =
        json_decode(
            file_get_contents(
                $experienceFile
            ),
            true
        ) ?: [];

}

if(empty($experience)){

    exit(
        "NO EXPERIENCE FOUND"
    );

}

/*
|--------------------------------------------------------------------------
| Find Best Pattern
|--------------------------------------------------------------------------
*/

$bestKey = null;

$bestTrust = 0;

foreach($experience as $key=>$row){

    $trust =
        $row['trust']
        ?? 0;

    $total =
        $row['total']
        ?? 0;

    /*
    | Ignore low sample patterns
    */

    if($total < 1){
        continue;
    }

    if($trust > $bestTrust){

        $bestTrust =
            $trust;

        $bestKey =
            $key;

    }

}

/*
|--------------------------------------------------------------------------
| Fallback
|--------------------------------------------------------------------------
*/

if(!$bestKey){

    $adaptive = [

        'mode' =>
            'WAIT',

        'confidence' =>
            0,

        'active_pattern' =>
            null,

        'updated_at' =>
            date(
                'Y-m-d H:i:s'
            )

    ];

    file_put_contents(

        $adaptiveFile,

        json_encode(
            $adaptive,
            JSON_PRETTY_PRINT
        )

    );

    exit(
        "WAIT MODE"
    );

}

/*
|--------------------------------------------------------------------------
| Parse Pattern
|--------------------------------------------------------------------------
*/

$best =
    $experience[$bestKey];

$pattern =
    $best['pattern']
    ?? '';

$direction =
    $best['direction']
    ?? '';

$trust =
    round(
        $best['trust']
        ?? 0,
        2
    );

$total =
    $best['total']
    ?? 0;

/*
|--------------------------------------------------------------------------
| Determine Mode
|--------------------------------------------------------------------------
*/

$mode = 'WAIT';

if(
    $trust >= 70
){
    $mode = 'FOLLOW';
}
elseif(
    $trust >= 55
){
    $mode = 'OBSERVE';
}
else{
    $mode = 'DEFENSIVE';
}

/*
|--------------------------------------------------------------------------
| Save Adaptive
|--------------------------------------------------------------------------
*/

$adaptive = [

    'mode' =>
        $mode,

    'confidence' =>
        $trust,

    'active_pattern' =>
        $pattern,

    'active_direction' =>
        $direction,

    'sample_size' =>
        $total,

    'updated_at' =>
        date(
            'Y-m-d H:i:s'
        )

];

file_put_contents(

    $adaptiveFile,

    json_encode(
        $adaptive,
        JSON_PRETTY_PRINT
    )

);

/*
|--------------------------------------------------------------------------
| Console Output
|--------------------------------------------------------------------------
*/

echo PHP_EOL;

echo "=================================\n";
echo " FEEDBACK ADAPTIVE ENGINE\n";
echo "=================================\n";

echo "Pattern : "
    .$pattern
    ."\n";

echo "Direction : "
    .$direction
    ."\n";

echo "Trust : "
    .$trust
    ."%\n";

echo "Samples : "
    .$total
    ."\n";

echo "Mode : "
    .$mode
    ."\n";

echo "=================================\n";