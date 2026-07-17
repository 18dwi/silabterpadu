<?php
// Extract Low Stock HTML from Superadmin
$pathSuper = 'resources/views/dashboard-superadmin.blade.php';
$contentSuper = file_get_contents($pathSuper);
preg_match('/<!-- Low stock materials -->.*?<\/table>\s*<\/div>\s*<\/div>/s', $contentSuper, $lowStockMatches);

if (!empty($lowStockMatches)) {
    $lowStockHtml = $lowStockMatches[0];
    
    // Inject into Laboran
    $pathLab = 'resources/views/dashboard-laboran.blade.php';
    $contentLab = file_get_contents($pathLab);
    
    $targetLab2 = "<div x-show=\"activeTab === 'rekap_bahan'\" class=\"space-y-6\" x-transition>";
    if (strpos($contentLab, $targetLab2) !== false && strpos($contentLab, '<!-- Low stock materials -->') === false) {
        $replaceLab2 = $targetLab2 . "\n\n                    " . $lowStockHtml;
        $contentLab = str_replace($targetLab2, $replaceLab2, $contentLab);
        file_put_contents($pathLab, $contentLab);
        echo "Successfully added to Laboran!\n";
    } else {
        echo "Target not found in Laboran or already exists.\n";
    }
} else {
    echo "Could not extract Low stock block from Superadmin.\n";
}
