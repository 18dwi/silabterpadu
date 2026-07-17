<?php
$content = file_get_contents('app/Http/Controllers/DashboardController.php');

$target = '$allTransactionsForDeletion = $allTransactionsQuery->get();';
$replacement = '$allTransactionsForDeletion = $allTransactionsQuery->get();

        // For deletion (Room Bookings)
        $allRoomBookingsQuery = \App\Models\RoomBooking::with([\'user\', \'items.room\'])->orderBy(\'created_at\', \'desc\');
        if ($user->role === \'admin\' || $user->role === \'laboran\') {
            $allRoomBookingsQuery->where(\'jurusan\', $user->jurusan);
        } elseif ($jurusan !== \'semua\') {
            $allRoomBookingsQuery->where(\'jurusan\', $jurusan);
        }
        $allRoomBookingsForDeletion = $allRoomBookingsQuery->get();';

$content = str_replace($target, $replacement, $content);

$targetReturn = "'allTransactionsForDeletion',";
$replacementReturn = "'allTransactionsForDeletion',\n            'allRoomBookingsForDeletion',";
$content = str_replace($targetReturn, $replacementReturn, $content);

file_put_contents('app/Http/Controllers/DashboardController.php', $content);
echo "DashboardController updated!\n";
