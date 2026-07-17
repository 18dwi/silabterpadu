<?php
$html = file_get_contents('resources/views/dashboard-superadmin.blade.php');
$lines = explode("\n", $html);
$open = 4; // Start at 4 because flex-grow is 4
foreach($lines as $i => $line) {
    if ($i < 400) {
        $open += substr_count($line, '<div');
        $open -= substr_count($line, '</div');
        continue;
    }
    if ($i > 520) break;
    $open += substr_count($line, '<div');
    $open -= substr_count($line, '</div');
    echo ($i+1) . " DEPTH: $open | " . trim(substr($line, 0, 50)) . "\n";
}
