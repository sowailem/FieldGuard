<?php

namespace Sowailem\FieldGuard\Commands;

use Illuminate\Console\Command;
use Sowailem\FieldGuard\Repositories\FieldGuardRuleRepository;

class ClearCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fieldguard:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the FieldGuard rules cache';

    /**
     * Execute the console command.
     */
    public function handle(FieldGuardRuleRepository $repository)
    {
        $repository->clearCache();
        $this->info('FieldGuard rules cache cleared successfully.');
    }
}
