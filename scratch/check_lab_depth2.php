<?php
$html = file_get_contents('resources/views/dashboard-laboran.blade.php');
$lines = explode("\n", $html);
$open = 0;
foreach($lines as $i => $line) {
    $open += substr_count($line, '<div');
    $open -= substr_count($line, '</div');
    if (strpos($line, 'x-show="activeTab ===') !== false) {
        echo "LINE " . ($i+1) . " DEPTH: $open TEXT: " . trim($line) . "\n";
    }
}
