/**
 * Si-Lab Keperawatan: Google Sheets Real-Time Sync Script
 * 
 * Instructions:
 * 1. Open your Google Sheet.
 * 2. Click "Extensions" > "Apps Script".
 * 3. Delete any code in the editor and paste this code.
 * 4. Replace the LARAVEL_APP_URL placeholder with your application's domain/URL.
 * 5. Click "Save" (floppy disk icon).
 * 6. Click "Deploy" > "New deployment".
 * 7. Choose type: "Web app".
 * 8. Set Configuration:
 *    - Execute as: "Me" (your account)
 *    - Who has access: "Anyone" (crucial for Laravel webhook access)
 * 9. Copy the generated Web App URL and add it to your Laravel .env file:
 *    GOOGLE_SHEET_WEBAPP_URL=https://script.google.com/macros/s/xxxx/exec
 * 10. To enable sheet-to-app real-time updates, setup a trigger:
 *     - In the left sidebar of Apps Script, click the clock icon (Triggers).
 *     - Click "Add Trigger".
 *     - Choose function to run: "onSheetEdit"
 *     - Select event source: "From spreadsheet"
 *     - Select event type: "On edit"
 *     - Click "Save" and authorize permissions.
 */

// Replace this with your public Laravel application URL
var LARAVEL_APP_URL = "http://lab-keperawatan.test"; 

/**
 * 1. APP -> SHEET: Handles incoming updates from Laravel application.
 */
function doPost(e) {
  try {
    var payload = JSON.parse(e.postData.contents);
    var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
    var data = sheet.getDataRange().getValues();
    
    var kodeBarang = payload.kode_barang;
    var rowFound = -1;
    
    // Find row with matching kode_barang (Column A / Column index 0)
    for (var i = 1; i < data.length; i++) {
      if (data[i][0] == kodeBarang) {
        rowFound = i + 1; // 1-indexed row number
        break;
      }
    }
    
    // Map values to row columns:
    // A: kode_barang, B: nama_barang, C: kategori, D: merk_tipe, E: stok_total, 
    // F: stok_tersedia, G: jumlah_baik, H: jumlah_perbaikan, I: jumlah_rusak, J: lokasi_rak
    var rowValues = [
      payload.kode_barang,
      payload.nama_barang,
      payload.kategori,
      payload.merk_tipe,
      payload.stok_total,
      payload.stok_tersedia,
      payload.jumlah_baik,
      payload.jumlah_perbaikan,
      payload.jumlah_rusak,
      payload.lokasi_rak
    ];
    
    if (rowFound !== -1) {
      // Update existing row
      sheet.getRange(rowFound, 1, 1, rowValues.length).setValues([rowValues]);
    } else {
      // Append new row
      sheet.appendRow(rowValues);
    }
    
    return ContentService.createTextOutput(JSON.stringify({ status: "success", message: "Row synchronized successfully" }))
                         .setMimeType(ContentService.MimeType.JSON);
                         
  } catch (err) {
    return ContentService.createTextOutput(JSON.stringify({ status: "error", error: err.toString() }))
                         .setMimeType(ContentService.MimeType.JSON);
  }
}

/**
 * 2. SHEET -> APP: Triggered when cells are edited manually inside the Google Sheet.
 */
function onSheetEdit(e) {
  try {
    var range = e.range;
    var sheet = range.getSheet();
    var row = range.getRow();
    
    // Ignore header edits (Row 1)
    if (row === 1) return;
    
    // Get row values
    var rowValues = sheet.getRange(row, 1, 1, 10).getValues()[0];
    
    var kodeBarang = rowValues[0];
    if (!kodeBarang) return; // Do nothing if code is empty
    
    var payload = {
      kode_barang: String(rowValues[0]),
      nama_barang: String(rowValues[1]),
      kategori: String(rowValues[2]).toLowerCase(),
      merk_tipe: String(rowValues[3]),
      stok_total: Number(rowValues[4]) || 0,
      stok_tersedia: Number(rowValues[5]) || 0,
      jumlah_baik: Number(rowValues[6]) || 0,
      jumlah_perbaikan: Number(rowValues[7]) || 0,
      jumlah_rusak: Number(rowValues[8]) || 0,
      lokasi_rak: String(rowValues[9] || 'Rak Default')
    };
    
    // Send HTTP POST webhook to Laravel backend
    var url = LARAVEL_APP_URL + "/api/sheets-webhook";
    var options = {
      method: "post",
      contentType: "application/json",
      payload: JSON.stringify(payload),
      muteHttpExceptions: true
    };
    
    UrlFetchApp.fetch(url, options);
    
  } catch (err) {
    Logger.log("Webhook sync failed: " + err.toString());
  }
}
