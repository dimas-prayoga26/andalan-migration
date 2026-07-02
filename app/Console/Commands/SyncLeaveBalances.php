<?php

namespace App\Console\Commands;

use Database\Seeders\LeaveBalanceSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('leave-balances:sync')]
#[Description('Synchronize leave balances based on employment start date and leave usage history')]
class SyncLeaveBalances extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        app(LeaveBalanceSeeder::class)->run();

        $this->info('Leave balances synchronized successfully.');

        return self::SUCCESS;
    }
}
