<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Caiji\CaijiController;

class caiji extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'caiji {maxpage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collecting video data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        echo 'Collecting starting...';
    }

    public function __destruct()
    {
echo '
Collecting ending...
';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      $maxpage = $this->argument('maxpage'); 
      $caiji = new CaijiController();
      $caiji->auto_apis($maxpage);
    }
}
