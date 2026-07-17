<?php
$file = 'resources/views/dashboard-superadmin.blade.php';
$content = file_get_contents($file);
$content = preg_replace('/<!-- TAB RUANGAN -->\s*(<\/div>\s*){5}/', '', $content);
file_put_contents($file, $content);
