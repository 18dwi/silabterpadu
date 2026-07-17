<?php
$content = file_get_contents('resources/views/dashboard-superadmin.blade.php');

// Add Download PDF & Excel buttons for Rekap Ruangan
$headerTarget = '                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Rekap Penggunaan Ruangan</h3>
                                    <p class="text-xs text-gray-500">Menampilkan statistik total mahasiswa dan total jam penggunaan dari ruangan laboratorium.</p>
                                </div>
                            </div>';

$headerReplacement = '                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Rekap Penggunaan Ruangan</h3>
                                    <p class="text-xs text-gray-500">Menampilkan statistik total mahasiswa dan total jam penggunaan dari ruangan laboratorium.</p>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route(\'superadmin.report.export-room-pdf\', [\'start_date\' => $startDate, \'end_date\' => $endDate, \'jurusan\' => $jurusan]) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 border border-red-600 text-xs font-semibold rounded text-red-600 hover:bg-red-50 transition duration-150 shadow-sm">
                                        📄 Download PDF
                                    </a>
                                    <a href="{{ route(\'superadmin.report.export-room-csv\', [\'start_date\' => $startDate, \'end_date\' => $endDate, \'jurusan\' => $jurusan]) }}" class="inline-flex items-center px-3 py-1.5 border border-green-600 text-xs font-semibold rounded text-white bg-green-600 hover:bg-green-700 transition duration-150 shadow-sm">
                                        📥 Download Excel
                                    </a>
                                </div>
                            </div>';
$content = str_replace($headerTarget, $headerReplacement, $content);

// Add Reset Button
$filterTarget = '<div class="flex items-end">
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm">
                                        Filter Rekap Ruangan
                                    </button>
                                </div>';

$filterReplacement = '<div class="flex items-end gap-2">
                                    <a href="{{ route(\'dashboard\', [\'active_tab\' => \'rekap_ruangan\']) }}" class="w-1/3 inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded text-xs font-semibold text-gray-600 bg-white hover:bg-gray-50 transition duration-150 shadow-sm">
                                        Reset
                                    </a>
                                    <button type="submit" class="w-2/3 inline-flex justify-center items-center px-4 py-2 border border-transparent rounded text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm">
                                        Filter
                                    </button>
                                </div>';
$content = str_replace($filterTarget, $filterReplacement, $content);

file_put_contents('resources/views/dashboard-superadmin.blade.php', $content);
echo "View updated!\n";
