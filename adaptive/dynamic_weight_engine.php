<?php

/*
|--------------------------------------------------------------------------
| AARM
| Dynamic Weight Engine V1
|--------------------------------------------------------------------------
*/

$accuracyFile =
    dirname(__DIR__).'/storage/brain_accuracy.json';

$outputFile =
    dirname(__DIR__).'/storage/brain_weights.json';

if(!file_exists($accuracyFile)){
    exit('brain_accuracy.json NOT FOUND');
}

$brains =
    json_decode(
        file_get_contents($accuracyFile),
        true
    ) ?: [];

if(empty($brains)){
    exit('NO BRAIN DATA');
}

$weights = [];

foreach($brains as $brainName=>$row){

    $wr =
        $row['wr']
        ?? 50;

    $sample =
        $row['total']
        ?? 0;

    /*
    |--------------------------------------------------------------------------
    | Sample Protection
    |--------------------------------------------------------------------------
    */

    if($sample < 10){

        $weight = 1.00;

    }else{

        /*
        |--------------------------------------------------------------------------
        | Weight Formula
        |--------------------------------------------------------------------------
        |
        | WR 50% = 1.00
        | WR 60% = 1.10
        | WR 70% = 1.20
        | WR 40% = 0.90
        | WR 30% = 0.80
        |--------------------------------------------------------------------------
        */

        $weight =
            1 +
            (
                ($wr - 50)
                / 100
            );

        /*
        |--------------------------------------------------------------------------
        | Clamp
        |--------------------------------------------------------------------------
        */

        $weight =
            max(
                0.50,
                min(
                    1.50,
                    round(
                        $weight,
                        2
                    )
                )
            );

    }

    $weights[$brainName] = [

        'wr' =>
            $wr,

        'sample' =>
            $sample,

        'weight' =>
            $weight,

        'updated_at' =>
            date('Y-m-d H:i:s')

    ];

}

/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

file_put_contents(

    $outputFile,

    json_encode(
        $weights,
        JSON_PRETTY_PRINT
    )

);

echo PHP_EOL;
echo "==================================\n";
echo " DYNAMIC WEIGHT ENGINE V1\n";
echo "==================================\n";

foreach($weights as $brain=>$row){

    echo
        $brain
        ." | WR="
        .$row['wr']
        ."% | SAMPLE="
        .$row['sample']
        ." | WEIGHT="
        .$row['weight']
        ."\n";

}

echo "==================================\n";
echo "Saved : brain_weights.json\n";
echo "==================================\n";