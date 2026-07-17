<?php
// 1. DASHBOARD CONTROLLER
$path = 'app/Http/Controllers/DashboardController.php';
$content = file_get_contents($path);

// Add lowStockMaterials to laboran
$laboranQuery = '        $lowStockMaterials = \App\Models\Item::where(\'kategori\', \'bahan\')
            ->where(\'stok_tersedia\', \'<\', 10)
            ->where(\'jurusan\', $user->jurusan)
            ->orderBy(\'stok_tersedia\')
            ->get();';

$content = str_replace(
    '$usedRoomItems = $usedRoomItems->orderBy(\'tanggal_mulai\')->get();',
    "\$usedRoomItems = \$usedRoomItems->orderBy('tanggal_mulai')->get();\n\n" . $laboranQuery,
    $content
);

$content = str_replace(
    "'usedRoomItems'",
    "'usedRoomItems',\n                'lowStockMaterials'",
    $content
);

file_put_contents($path, $content);

// 2. DASHBOARD SUPERADMIN
$pathSuper = 'resources/views/dashboard-superadmin.blade.php';
$contentSuper = file_get_contents($pathSuper);

// 2a. Reset buttons
// Filter Rekap Penggunaan Alat
$targetAlat = '<button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm">
                                        Filter Rekap Penggunaan Alat
                                    </button>';
$replaceAlat = '<div class="flex items-center gap-2 w-full">
                                        <a href="{{ route(\'dashboard\', [\'active_tab\' => \'laporan_rekap\']) }}" class="w-1/3 inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded text-xs font-semibold text-gray-600 bg-white hover:bg-gray-50 transition duration-150 shadow-sm">
                                            Reset
                                        </a>
                                        <button type="submit" class="w-2/3 inline-flex justify-center items-center px-4 py-2 border border-transparent rounded text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm">
                                            Filter
                                        </button>
                                    </div>';
$contentSuper = str_replace($targetAlat, $replaceAlat, $contentSuper);

// Filter Rekap Penggunaan Bahan
$targetBahan = '<button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm">
                                        Filter Rekap Penggunaan Bahan
                                    </button>';
$replaceBahan = '<div class="flex items-center gap-2 w-full">
                                        <a href="{{ route(\'dashboard\', [\'active_tab\' => \'rekap_bahan\']) }}" class="w-1/3 inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded text-xs font-semibold text-gray-600 bg-white hover:bg-gray-50 transition duration-150 shadow-sm">
                                            Reset
                                        </a>
                                        <button type="submit" class="w-2/3 inline-flex justify-center items-center px-4 py-2 border border-transparent rounded text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm">
                                            Filter
                                        </button>
                                    </div>';
$contentSuper = str_replace($targetBahan, $replaceBahan, $contentSuper);

// 2b. Shrink sidebar width
$contentSuper = preg_replace(
    '/<div class="w-48 md:w-64 flex-shrink-0">/',
    '<div class="w-44 md:w-52 flex-shrink-0">',
    $contentSuper
);

// We should also grab the Low Stock Materials HTML to put in laboran
preg_match('/<!-- LOW STOCK MATERIALS -->.*?<\/div>\s*<\/div>\s*<\/div>/s', $contentSuper, $lowStockMatches);
$lowStockHtml = '';
if (!empty($lowStockMatches)) {
    $lowStockHtml = $lowStockMatches[0];
}

file_put_contents($pathSuper, $contentSuper);

// 3. DASHBOARD LABORAN
if ($lowStockHtml) {
    $pathLab = 'resources/views/dashboard-laboran.blade.php';
    $contentLab = file_get_contents($pathLab);
    
    // Find TAB REKAP BAHAN
    // It should be `<div x-show="activeTab === 'rekap_bahan'" ...>`
    // Then there is a `<div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">` inside it maybe?
    // Let's just insert it right after the `<div x-show="activeTab === 'rekap_bahan'" ...>`
    
    $targetLab = "<div x-show=\"activeTab === 'rekap_bahan'\" class=\"space-y-6\" x-transition style=\"display: none;\">";
    $replaceLab = $targetLab . "\n\n                    " . $lowStockHtml;
    
    // Check if it already exists to avoid duplicates
    if (strpos($contentLab, '<!-- LOW STOCK MATERIALS -->') === false) {
        $contentLab = str_replace($targetLab, $replaceLab, $contentLab);
        // Sometimes style="display: none;" might not be there or might be different
        if (strpos($contentLab, '<!-- LOW STOCK MATERIALS -->') === false) {
            $targetLab2 = "<div x-show=\"activeTab === 'rekap_bahan'\" class=\"space-y-6\" x-transition>";
            $replaceLab2 = $targetLab2 . "\n\n                    " . $lowStockHtml;
            $contentLab = str_replace($targetLab2, $replaceLab2, $contentLab);
        }
        file_put_contents($pathLab, $contentLab);
    }
}

echo "All tasks executed!\n";
