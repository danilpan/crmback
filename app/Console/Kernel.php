<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Psy\Command\Command;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\QueuesChkStatuses::class,
        Commands\SearchCreateIndex::class,
        Commands\SearchReindex::class,
        Commands\CrmkaMigrateDb::class,
        Commands\CrmkaMigrateStructure::class,
        Commands\SetRelatedProducts::class,
        Commands\SetMobizonStatus::class,
        Commands\SetSmscStatus::class,
        Commands\ApiKit::class,
        Commands\LogQueueStateHistory::class,
        Commands\OrdersCallCheck::class,
        Commands\CallsAttemptsReport::class,
        // Commands\ScriptsQualityReport::class,
        Commands\ApprovedCallDurationReport::class,
        Commands\RejectedCallDurationReport::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        
        //$schedule->command('SetRelatedProducts:set')
          //        ->dailyAt('3:15');

        $schedule->command('SetMobizonStatus:set')
                  ->hourly();

        $schedule->command('SetSmscStatus:set')
                  ->hourly();

        $schedule->command('LogQueueStateHistory:log')
                  ->everyMinute();

        $schedule->command('OrdersCallCheck:check')
                  ->hourly();
                  
        $schedule->command('ApiKetKZ:set --type=send')
                  ->everyThirtyMinutes();

        $schedule->command('ApiKetKZ:set --type=check')
                  ->hourlyAt(17);

        $schedule->command('SendAutoSms:set --type=status_3_4_KzKrg_11')
                  ->hourlyAt(27);

        $schedule->command('SendAutoSms:set --type=status_3_4_KzKrg_12')
                  ->hourlyAt(17);

        $schedule->command('SendAutoSms:set --type=status_3_4_KzKrg_13')
                  ->hourlyAt(7);

        $schedule->command('SendAutoSms:set --type=status_3_4_KzKrg_14')
                  ->hourlyAt(37);

        $schedule->command('SendAutoSms:set --type=after_certain_days')
                  ->dailyAt('5:30');

        $schedule->command('SendAutoSms:set --type=package_arrived')
                  ->dailyAt('5:35');

        $schedule->command('SetTopProjects:set')
                  ->cron('11 */4 * * *');
                  
        $schedule->command('CallsAttemptsReport:send')
                  ->daily();

        // $schedule->command('ScriptsQualityReport:send')
        //           ->weekly();

        $schedule->command('ApprovedCallDurationReport:send')
                  ->monthly();

        $schedule->command('RejectedCallDurationReport:send')
                  ->monthly();

        $schedule->command('GetKcStat:set --function=approves_per_hour')
                  ->dailyAt('12:00');

        $schedule->command('queue:chk_statuses')
                  ->hourly();

        $schedule->command('GetKcStat:set --function=staff_hours')
                  ->dailyAt('00:00');

        $schedule->command('GetKcStat:set --function=add_sales')
                  ->dailyAt('00:00');

        $schedule->command('GetKcStat:set --function=phoned_orders')
            ->dailyAt('00:00');

        $schedule->command('GetKcStat:set --function=manual_calls')
                ->dailyAt('00:00');
        
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
