<?php
$html = file_get_contents('resources/views/dashboard-superadmin.blade.php');
$lines = explode("\n", $html);
$open = 0;
foreach($lines as $i => $line) {
    if($i>150) break;
    $open += substr_count($line, '<div');
    $open -= substr_count($line, '</div');
    echo ($i+1) . " DEPTH: $open | " . trim(substr($line, 0, 50)) . "\n";
}
