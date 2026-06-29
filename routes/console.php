<?php

use App\Console\Commands\ClearSuccessQuotaSchedules;
use App\Console\Commands\ResetQuota;
use App\Console\Commands\ScheduleAddQuota;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// * daily quota reset scheduler
Schedule::command(ResetQuota::class)->dailyAt('00:00')->timezone('Asia/Jakarta');

// * automated add quota scheduler
Schedule::command(ScheduleAddQuota::class)->dailyAt('00:03')->timezone('Asia/Jakarta');

// * monthly quota schedules clearance for succes status
Schedule::command(ClearSuccessQuotaSchedules::class)->monthlyOn(1, '00:06')->timezone('Asia/Jakarta');
// Schedule::command(ClearSuccessQuotaSchedules::class)->dailyAt('10:20')->timezone('Asia/Jakarta');
