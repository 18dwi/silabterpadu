<?php
$lines = explode("\n", file_get_contents('resources/views/dashboard-superadmin.blade.php'));
echo "Lines 990-1030:\n";
for($i=990; $i<=1030; $i++) echo $i.': '.$lines[$i-1]."\n";
