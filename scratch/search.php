<?php
$c = file_get_contents('resources/views/dashboard-superadmin.blade.php');
$lines = explode("\n", $c);
foreach($lines as $i => $l) {
    if(strpos($l, 'action="{{ route(\'dashboard\') }}"') !== false) {
        echo "Line " . ($i+1) . ": " . trim($l) . "\n";
    }
}
