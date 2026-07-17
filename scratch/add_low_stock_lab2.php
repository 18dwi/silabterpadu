<?php
$pathLab = 'resources/views/dashboard-laboran.blade.php';
$contentLab = file_get_contents($pathLab);

$pathSuper = 'resources/views/dashboard-superadmin.blade.php';
$contentSuper = file_get_contents($pathSuper);
preg_match('/<div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">\s*<h3 class="text-sm font-bold text-red-600 mb-3 flex items-center gap-1\.5">.*?<\/table>\s*<\/div>\s*<\/div>/s', $contentSuper, $lowStockMatches);

if (!empty($lowStockMatches)) {
    $lowStockHtml = $lowStockMatches[0];
    
    // Add margin bottom to lowStockHtml
    $lowStockHtml = str_replace('<div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">', '<div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">', $lowStockHtml);

    $targetLab = '<div x-show="activeTab === \'inventaris_bahan\'" class="space-y-6">';
    
    // Check if it exists
    if (strpos($contentLab, $targetLab) !== false) {
        $replacement = $targetLab . "\n\n                    <!-- LOW STOCK MATERIALS -->\n                    " . $lowStockHtml;
        
        if (strpos($contentLab, '<!-- LOW STOCK MATERIALS -->') === false) {
            $contentLab = str_replace($targetLab, $replacement, $contentLab);
            file_put_contents($pathLab, $contentLab);
            echo "Successfully added to Laboran (Inventaris Bahan)!\n";
        } else {
            echo "Already added.\n";
        }
    } else {
        echo "Could not find target in Laboran.\n";
    }
} else {
    echo "Could not find low stock html in Superadmin.\n";
}
