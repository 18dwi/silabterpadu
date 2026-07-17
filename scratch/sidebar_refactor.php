<?php
$content = file_get_contents('resources/views/dashboard-superadmin.blade.php');

preg_match('/<!-- Sidebar Menus -->.*?<\/div>\s*<\/div>\s*<\/div>\s*<!-- CONTENT AREA/s', $content, $sidebarMatches);
if (!empty($sidebarMatches)) {
    $sidebar = $sidebarMatches[0];
    
    // Extract individual buttons
    preg_match('/<!-- Tab: Rekap Penggunaan Alat.*?<\/button>/s', $sidebar, $btnAlat);
    preg_match('/<!-- Tab: Kelola Akun Pengguna.*?<\/button>/s', $sidebar, $btnAkun);
    preg_match('/<!-- Tab: Rekap Penggunaan Bahan.*?<\/button>/s', $sidebar, $btnBahan);
    preg_match('/<!-- Tab: Rekap Penggunaan Ruangan.*?<\/button>/s', $sidebar, $btnRuangan);
    preg_match('/<!-- Tab: Bersihkan Riwayat.*?<\/button>/s', $sidebar, $btnRiwayat);
    
    // Decrease padding/font size of the buttons to fit in 1 page.
    $btnAlat = str_replace('py-2.5', 'py-1.5', str_replace('text-xs', 'text-[11px]', $btnAlat[0]));
    $btnAkun = str_replace('py-2.5', 'py-1.5', str_replace('text-xs', 'text-[11px]', $btnAkun[0]));
    $btnBahan = str_replace('py-2.5', 'py-1.5', str_replace('text-xs', 'text-[11px]', $btnBahan[0]));
    $btnRuangan = str_replace('py-2.5', 'py-1.5', str_replace('text-xs', 'text-[11px]', $btnRuangan[0]));
    $btnRiwayat = str_replace('py-2.5', 'py-1.5', str_replace('text-xs', 'text-[11px]', $btnRiwayat[0]));
    
    $newSidebar = "<!-- Sidebar Menus -->
                        <div class=\"w-full space-y-1 pt-3 border-t border-gray-100\">
                            " . $btnRuangan . "
                            " . $btnAlat . "
                            " . $btnBahan . "
                            " . $btnAkun . "
                            " . $btnRiwayat . "
                        </div>
                    </div>
                </div>

                <!-- CONTENT AREA";
    
    $content = str_replace($sidebarMatches[0], $newSidebar, $content);
}

file_put_contents('resources/views/dashboard-superadmin.blade.php', $content);
echo "Sidebar layout updated!\n";
