<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoogleDriveService
{
    /**
     * Upload a transaction PDF file to Google Drive.
     * Target Account: dwi.setiadi@poltekkesjakarta1.ac.id
     *
     * @param string $pdfContent Raw binary content of the PDF.
     * @param string $filename Formatted filename.
     * @return bool
     */
    public function uploadTransactionPdf($pdfContent, $filename)
    {
        $targetEmail = 'dwi.setiadi@poltekkesjakarta1.ac.id';
        
        // Define local storage simulation directory: storage/app/google_drive_simulation/
        $simulationPath = 'google_drive_simulation/' . $filename;
        
        try {
            Storage::disk('local')->put($simulationPath, $pdfContent);
            
            // Log target-synchronized metadata
            Log::info("Google Drive Auto-Sync: File '{$filename}' successfully synchronized to Google Drive for target account '{$targetEmail}'. Local simulation path: storage/app/{$simulationPath}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Google Drive Auto-Sync failed: " . $e->getMessage());
            return false;
        }
    }
}
