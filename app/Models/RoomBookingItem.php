<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomBookingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_booking_id',
        'room_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'waktu_mulai',
        'waktu_selesai',
    ];

    protected $casts = [
        'tanggal_mulai'  => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function booking()
    {
        return $this->belongsTo(RoomBooking::class, 'room_booking_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Hitung durasi pemakaian ruangan berdasarkan rule kompleks
     */
    public function calculateUsageHours()
    {
        $start = \Carbon\Carbon::parse($this->tanggal_mulai);
        $end = \Carbon\Carbon::parse($this->tanggal_selesai);
        
        $startTime = \Carbon\Carbon::parse($this->waktu_mulai);
        $endTime = \Carbon\Carbon::parse($this->waktu_selesai);
        
        $diffDays = $start->diffInDays($end);

        $limitZero = function ($value) {
            return max(0, $value);
        };

        // Aturan 5: Cek apakah murni hanya di weekend
        $isOnlyWeekend = true;
        $temp = $start->copy();
        while ($temp->lte($end)) {
            if (!$temp->isWeekend()) {
                $isOnlyWeekend = false;
                break;
            }
            $temp->addDay();
        }

        // Aturan 1: Tanggal mulai = tanggal selesai
        if ($diffDays == 0) {
            if (!$start->isWeekend() || $isOnlyWeekend) {
                return $limitZero($endTime->diffInMinutes($startTime) / 60);
            }
            return 0;
        }

        // Aturan 2: Selama 2 hari
        if ($diffDays == 1) {
            $day1End = \Carbon\Carbon::parse('16:00');
            $day2Start = \Carbon\Carbon::parse('07:00');
            
            $day1Hours = 0;
            if (!$start->isWeekend() || $isOnlyWeekend) {
                // Jika startTime setelah 16:00, diffInMinutes bisa negatif jika tidak dibalik atau kalau pakai absolute.
                // Jika startTime 17:00, 16:00 - 17:00 = negatif. limitZero akan jadi 0.
                // Note: diffInMinutes() by default is absolute, but if we do $day1End->diffInMinutes($startTime, false), it gives negative if startTime is after day1End.
                $diff1 = $day1End->diffInMinutes($startTime, false) * -1; // -1 karena (day1End - startTime)
                $day1Hours = $limitZero($diff1 / 60);
            }

            $day2Hours = 0;
            if (!$end->isWeekend() || $isOnlyWeekend) {
                $diff2 = $endTime->diffInMinutes($day2Start, false) * -1; // (endTime - day2Start)
                $day2Hours = $limitZero($diff2 / 60);
            }
            
            return $day1Hours + $day2Hours;
        }

        // Aturan 3 & 4: Lebih dari 2 hari
        $day1End = \Carbon\Carbon::parse('16:00');
        $lastDayStart = \Carbon\Carbon::parse('07:00');
        
        $totalHours = 0;
        
        // Hari 1
        if (!$start->isWeekend() || $isOnlyWeekend) {
            $diff1 = $day1End->diffInMinutes($startTime, false) * -1;
            $totalHours += $limitZero($diff1 / 60);
        }
        
        // Hari Terakhir
        if (!$end->isWeekend() || $isOnlyWeekend) {
            $diff2 = $endTime->diffInMinutes($lastDayStart, false) * -1;
            $totalHours += $limitZero($diff2 / 60);
        }
        
        // Hari di tengah
        $tempDate = $start->copy()->addDay();
        while ($tempDate->lt($end)) {
            if (!$tempDate->isWeekend() || $isOnlyWeekend) {
                $totalHours += 9;
            }
            $tempDate->addDay();
        }
        
        return $totalHours;
    }
}
