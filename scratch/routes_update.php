<?php
$content = file_get_contents('routes/web.php');
$target = "Route::get('/superadmin/report/export-materials'";
$replacement = "Route::get('/superadmin/report/export-room-pdf', [DashboardController::class, 'exportRoomPdf'])->name('superadmin.report.export-room-pdf');\n    Route::get('/superadmin/report/export-room-csv', [DashboardController::class, 'exportRoomCsv'])->name('superadmin.report.export-room-csv');\n    Route::get('/superadmin/report/export-materials'";

$content = str_replace($target, $replacement, $content);
file_put_contents('routes/web.php', $content);
echo "Routes updated!\n";
