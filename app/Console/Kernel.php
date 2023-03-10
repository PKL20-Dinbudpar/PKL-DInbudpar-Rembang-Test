<?php

namespace App\Console;

use App\Models\Rekap;
use App\Models\Transaksi;
use App\Models\Wisata;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // schedule input data rekap for each wisata daily
        $schedule->call(function () {
            $wisata = Wisata::all();
            foreach ($wisata as $w) {
                $rekap = new Rekap;
                $rekap->tanggal = date('Y-m-d');
                $rekap->id_wisata = $w->id_wisata;
                $rekap->wisatawan_domestik = 0;
                $rekap->wisatawan_mancanegara = 0;
                $rekap->total_pendapatan = 0;
                $rekap->save();
            }
        })->dailyAt('22:58');
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     *
     * @return \DateTimeZone|string|null
     */
    protected function scheduleTimezone()
    {
        return 'Asia/Jakarta';
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
