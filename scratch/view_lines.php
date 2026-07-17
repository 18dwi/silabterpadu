<?php
$html = file_get_contents('resources/views/dashboard-superadmin.blade.php');
$lines = explode("\n", $html);
for($i=675; $i<=700; $i++) echo $i.': '.$lines[$i-1]."\n";
