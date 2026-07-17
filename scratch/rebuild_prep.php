<?php
$html = file_get_contents('resources/views/dashboard-superadmin.blade.php');

// 1. Get header to start of kelola_akun table
$part1 = substr($html, 0, strpos($html, '<div x-show="historyTab === \'alat\'">'));

// 2. The kelola_akun table is inside historyTab === 'alat'
$tableStart = strpos($html, '<div class="overflow-x-auto">', strpos($html, 'kelola_akun'));
$tableEnd = strpos($html, '</div>', strpos($html, '</table>', $tableStart));
$tableEnd = strpos($html, '</div>', $tableEnd + 1); // get the second </div> ?
// Wait, I can just use regex to extract the tables based on loops!

file_put_contents('scratch/rebuild.php', '...');
