<?php

namespace App\Console\Commands;

use App\Jobs\JobExtraerEstadoCuenta;
use Illuminate\Console\Command;

class ExtarerData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:extarer-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new JobExtraerEstadoCuenta);
    }
}
