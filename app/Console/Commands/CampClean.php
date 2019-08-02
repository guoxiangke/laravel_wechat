<?php

namespace App\Console\Commands;

use App\Models\Gamp;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CampClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gamp:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete 2 monthes ago gamps';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start = Carbon::now()->subMonths(2)->startOfMonth();
        Gamp::where('created_at', '<', $start)->forceDelete();
    }
}
