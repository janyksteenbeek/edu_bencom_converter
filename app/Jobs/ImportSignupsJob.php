<?php

namespace App\Jobs;

use App\Exceptions\UnsupportedReportException;
use App\Provider;
use App\Signup;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportSignupsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $filename;

    private $columnConfig = [
        'signup_timestamp' => 'Aanmelddatum',
        'provider_name' => 'ProviderNaam',
        'signup_type' => 'TypeAanmelding',
        'signup_age' => 'Leeftijd',
        'signup_city' => 'Woonplaats',
        'signup_packagetype' => 'TypePakket',
        'signup_color' => 'GroenGrijs',
    ];

    private $columns = [];

    private $providerCache = [];
    private $delimiter;

    /**
     * Create a new job instance.
     *
     * @param $delimeter
     * @param $filename
     */
    public function __construct($delimeter, $filename)
    {
        $this->filename = $filename;
        $this->delimiter = $delimeter;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->determineColumnHeaders();

        $this->loopReport();
    }

    private function determineColumnHeaders()
    {
        // Fetching first row of csv for headers.
        $rowHeader = trim(str_replace('﻿', null, (fgets(fopen($this->filename, 'r')))));
        $headers = str_getcsv($rowHeader, $this->delimiter);
        $columns = [];

        // Looping through headers.
        foreach($headers as $key => $header) {
            // Check if column header is found in column config.
            foreach ($this->columnConfig as $column => $versionValue) {
                if ($versionValue == $header) {

                    // Column header found.
                    $columns[$column] = $key;
                }
            }

        }

        if(count($this->columnConfig) != count($columns)) {
            throw new UnsupportedReportException();
        }

        $this->columns = $columns;
    }

    private function loopReport()
    {
        $rowCount = 0;

        // Opening stream.
        $handle = fopen($this->filename, "r");

        // Looping through file.
        while (! feof($handle)) {
            $rowCount++;

            $line = trim(fgets($handle));
            $data = str_getcsv($line, $this->delimiter);

            // Skipping column header.
            if($rowCount === 1) {
                continue;
            }

            // Skipping empty lines.
            if(count(array_filter($data)) == 0) {
                continue;
            }

            $providerName = $data[$this->columns['provider_name']];

            // Check if provider already exists or insert new provider.
            if(array_key_exists($providerName, $this->providerCache)) {
                $provider = $this->providerCache[$providerName];
            } else {
                $provider = Provider::firstOrCreate([
                    'name' => $providerName,
                ]);

                $this->providerCache[$providerName] = $provider;
            }

            // Inserting new sign up into database.
            Signup::create([
                'signed_up_at' => Carbon::parse($data[$this->columns['signup_timestamp']])->format('Y-m-d H:i:s'),
                'provider_id' => $provider->id,
                'type' => $data[$this->columns['signup_type']],
                'city' => $data[$this->columns['signup_city']],
                'package_type' => $data[$this->columns['signup_packagetype']],
                'age' => $data[$this->columns['signup_age']],
                'color' => $data[$this->columns['signup_color']] == 'groen' ? Signup::$COLOR_GREEN : Signup::$COLOR_GREY
            ]);

        }

        echo PHP_EOL . "Importing signups done.";
    }
}
