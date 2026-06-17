<?php

namespace Modules\AgenticAI\Console;

use Illuminate\Console\Command;
use Modules\AgenticAI\Jobs\IndexCompanyPolicies;

class IndexPoliciesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'agentic:index-policies';

    /**
     * The console command description.
     */
    protected $description = 'Index all company policies relationships into the Vector Store.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching IndexCompanyPolicies job...');
        
        // Dispatch synchronously for this command so we see output
        IndexCompanyPolicies::dispatchSync();
        
        $this->info('Indexing completed successfully.');
    }
}
