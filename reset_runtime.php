<?php

$storage = __DIR__.'/storage';

$keep = [

    'historical_ticks.json'

];

$files = glob($storage.'/*.json');

$reset = [];

foreach($files as $file){

    $name = basename($file);

    if(in_array($name,$keep)){
        continue;
    }

    file_put_contents(
        $file,
        json_encode(
            [],
            JSON_PRETTY_PRINT
        )
    );

    $reset[] = $name;

}

echo "<pre>";
echo "AARM RESET COMPLETE\n";
echo "====================\n";

foreach($reset as $f){

    echo $f."\n";

}

echo "\nHISTORICAL TICK PRESERVED\n";
echo "</pre>";
