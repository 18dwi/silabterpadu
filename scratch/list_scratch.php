<?php
// Re-apply all the scripts from July 12 to the base ZIP version
// We just extracted the base ZIP version (217114 bytes) 
// Now we need to run ALL scripts in order:
// 1. Step 5301 - patch.php - Added room booking tabs
// 2. Step 5331 - restructure.php - Added roomLevel1Tab, renamed Insidentil, restructured tabs
// 3. Step 5352 - modal_labels.php - Updated modal labels
// 4. Step 5371 - replace_file_content - Added x-show for roomLevel1Tab 
// 5. Step 6329 - multi_replace_file_content - Split Inventaris Lab into Alat+Bahan, remove secondary tabs
// 6. Step 6346 - multi_replace_file_content - Move LOW STOCK MATERIALS
// 7. Step 6353 - replace_file_content - Fix malformed tag

// Let's check what scripts are in scratch directory
$scratchDir = 'scratch/';
$files = glob($scratchDir . '*.php');
sort($files);
foreach ($files as $f) {
    $stat = stat($f);
    echo $f . " | " . date('Y-m-d H:i:s', $stat['mtime']) . "\n";
}
