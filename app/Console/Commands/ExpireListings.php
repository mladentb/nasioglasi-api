<?php

namespace App\Console\Commands;

use App\Models\Listing;
use Illuminate\Console\Command;

class ExpireListings extends Command
{
    protected $signature = 'listings:expire';
    protected $description = 'Mark expired listings as expired';

    public function handle(): int
    {
        $count = Listing::where('status', 'active')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Expired {$count} listings.");

        return self::SUCCESS;
    }
}
