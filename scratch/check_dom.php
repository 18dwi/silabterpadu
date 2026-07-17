<?php
$html = file_get_contents('resources/views/dashboard-superadmin.blade.php');
$lines = explode("\n", $html);
$indent = 0;
foreach($lines as $i => $line) {
    if (strpos($line, 'x-show="activeTab') !== false || strpos($line, 'flex-grow') !== false) {
        echo ($i+1) . ': ' . trim($line) . "\n";
    }
}
