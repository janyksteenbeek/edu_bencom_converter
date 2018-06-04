<?php

namespace App\Jobs;

use App\Exceptions\UnsupportedReportException;
use App\Press;
use App\Signup;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportPressJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $filename;

    private $columnConfig = [
        'date' => 'date',
        'medium_name' => 'medium_name',
        'category' => 'category',
        'location' => 'location',
        'title' => 'titel',
        'remarks' => 'remarks',
    ];

    private $columns = [];
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

            // Inserting new press expression into database.
            Press::create([
                'date' => Carbon::parse($data[$this->columns['date']])->format('Y-m-d'),
                'medium_name' => $data[$this->columns['medium_name']],
                'category' => $data[$this->columns['category']],
                'location' => $data[$this->columns['location']],
                'title' => $data[$this->columns['title']],
                'remarks' => $data[$this->columns['remarks']],
            ]);

        }

        echo PHP_EOL . "Importing press expressions done.";

    }
}
