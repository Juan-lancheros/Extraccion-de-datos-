<?php

namespace App\Console\Commands;

use App\Jobs\JobExtraerPagoProveedores;
use Illuminate\Console\Command;

class PagoProveedores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pago-proveedores';

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
        dispatch(new JobExtraerPagoProveedores);
    }
}
