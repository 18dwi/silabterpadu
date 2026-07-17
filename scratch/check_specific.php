<?php
$html = file_get_contents('resources/views/dashboard-superadmin.blade.php');
$lines = explode("\n", $html);
$open = 4;
foreach($lines as $i => $line) {
    if ($i < 137) continue;
    $open += substr_count($line, '<div');
    $open -= substr_count($line, '</div');
    if ($i == 313 || $i == 401 || $i == 505 || $i == 595 || $i == 688 || $i == 692) {
        echo ($i+1) . ' DEPTH: ' . $open . ' | ' . trim(substr($line, 0, 50)) . "\n";
    }
}
