<?php

namespace App\Console\Commands;

use App\Jobs\JobExtraerDetallesBancarios;
use Illuminate\Console\Command;

class DetallesBancarios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:detalles-bancarios';

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
        dispatch(new JobExtraerDetallesBancarios);
    }
}
