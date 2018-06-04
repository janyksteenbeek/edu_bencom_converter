<?php

namespace App\Console\Commands;

use App\Jobs\ImportSignupsJob;
use Illuminate\Console\Command;

class ParseSignupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:signups {delimeter} {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse signups and providers from a CSV.';

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
        $filename = $this->argument('filename');

        if(! \File::exists($filename)) {
            $this->error('File not found.');
            return;
        }

        dispatch(new ImportSignupsJob($this->argument('delimeter'), $filename));
    }
}
