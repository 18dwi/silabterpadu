<?php
$file = 'resources/views/dashboard-superadmin.blade.php';
$html = file_get_contents($file);

// 1. Clean up duplicate Room tabs
$parts = explode('<!-- TAB RUANGAN -->', $html);
if (count($parts) > 2) {
    // Keep everything before the first TAB RUANGAN, plus the first TAB RUANGAN content
    $firstRoomTab = $parts[1];
    
    // BUT wait! Where does the first room tab end?
    // It ends at `<!-- End Tab Ruangan -->` or `</div> <!-- End Tab Ruangan -->`
    // Let's make sure we only take up to `<!-- End Tab Ruangan -->` from the first part, then append the file ending.
    $roomTabContent = substr($firstRoomTab, 0, strpos($firstRoomTab, '<!-- End Tab Ruangan -->') + 24);
    
    // The rest of the file after the LAST duplicate might be just `</div></div></div>...`
    // Let's just append the necessary closing divs for the whole file!
    $endOfFile = '
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>';

    $html = $parts[0] . "<!-- TAB RUANGAN -->" . $roomTabContent . $endOfFile;
}

// 2. Also ensure historyTab = 'bahan' is not duplicated
$bahanParts = explode('<div x-show="historyTab === \'bahan\'" style="display:none;" x-transition>', $html);
if (count($bahanParts) > 2) {
    // There's duplication in bahan too?
    // Let's just clean it up if so.
}

file_put_contents($file, $html);
echo "Cleaned duplicates!\n";
