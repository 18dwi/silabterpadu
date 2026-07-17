<?php
$html = file_get_contents('resources/views/dashboard-superadmin.blade.php');
$lines = explode("\n", $html);
for($i=430; $i<=510; $i++) echo $i.': '.$lines[$i-1]."\n";
