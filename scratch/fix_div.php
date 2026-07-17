<?php
$file = 'resources/views/dashboard-superadmin.blade.php';
$content = file_get_contents($file);

$search = '                        </div>

                    <!-- TAB REKAPITULASI PENGGUNAAN RUANGAN -->';
$replace = '                        </div>
                    </div>

                    <!-- TAB REKAPITULASI PENGGUNAAN RUANGAN -->';

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    file_put_contents($file, $content);
    echo "Fixed layout successfully!\n";
} else {
    echo "Target string not found, trying regex...\n";
    $content = preg_replace('/(<\!-- TAB REKAPITULASI PENGGUNAAN RUANGAN -->)/', "                    </div>\n\n                    $1", $content, 1);
    file_put_contents($file, $content);
    echo "Fixed via regex.\n";
}
