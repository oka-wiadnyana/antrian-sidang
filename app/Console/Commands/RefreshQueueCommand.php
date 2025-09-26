<?php

namespace App\Console\Commands;

use App\Events\RefreshQueuePage;
use Illuminate\Console\Command;

class RefreshQueueCommand extends Command
{
    protected $signature = 'queue:refresh';
    protected $description = 'Refresh queue page untuk semua client';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Trigger refresh event
        event(new RefreshQueuePage());

        $this->info('Queue refresh event berhasil dikirim pada ' . now());

        return Command::SUCCESS;
    }
}
