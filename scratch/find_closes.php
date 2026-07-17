<?php
$html = file_get_contents('resources/views/dashboard-superadmin.blade.php');
$lines = explode("\n", $html);
$open = 0;
foreach($lines as $i => $line) {
    if (strpos($line, 'flex-grow') !== false) {
        $open += substr_count($line, '<div');
        $open -= substr_count($line, '</div');
        echo "FLEX-GROW START: " . ($i+1) . " DEPTH: $open\n";
        continue;
    }
    $open += substr_count($line, '<div');
    $open -= substr_count($line, '</div');
    
    if ($open < 3 && $i > 137) {
        echo "DEPTH DROPPED BELOW 3 AT LINE " . ($i+1) . " (Current depth: $open): " . trim($line) . "\n";
    }
}
