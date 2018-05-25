<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Caiji\mgtvController;

class mgtv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mgtv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collecting mgtv data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      $caiji = new mgtvController();
      $caiji->collection_mgtv();
    }
}
