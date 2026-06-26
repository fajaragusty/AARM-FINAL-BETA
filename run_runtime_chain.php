<?php

$php =
    'E:\\xampp\\php\\php.exe';

$jobs = [

    __DIR__.'/adaptive/brain_accuracy_tracker.php',
    __DIR__.'/adaptive/brain_performance_tracker.php',
    __DIR__.'/adaptive/dynamic_weight_engine.php',

    __DIR__.'/engines/experience_engine.php',
    __DIR__.'/engines/trust_engine.php',
    __DIR__.'/engines/adaptive_engine.php',

    __DIR__.'/engines/brain_consensus_engine.php',
    __DIR__.'/engines/brain_consensus_engine_v3.php',

    __DIR__.'/engines/signal_generator.php'

];

echo "<pre>";

foreach($jobs as $job){

    echo "\n========================\n";
    echo basename($job)."\n";
    echo "========================\n";

    $cmd =
        "\"".$php."\" ".
        "\"".$job."\"";

    echo shell_exec($cmd);

}

echo "\nDONE\n";
echo "</pre>";
