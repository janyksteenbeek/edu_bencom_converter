<?php

namespace App\Exceptions;

class UnsupportedReportException extends \Exception
{
    public $message = 'Unsupported report. Column mismatch?';
}