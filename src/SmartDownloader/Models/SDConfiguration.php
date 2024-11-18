<?php

namespace SmartDownloader\Models;

use SmartDownloader\Enumerators\RateExceedAction;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\DataExceptionEnum;

class SDConfiguration{

    public string $downloadDir = 'downloads';
    public string $temp_dir = 'temp';
    public int $maxDownloads = 5;
    public int $retry_attempts = 5;
    public RateExceedAction $rate_Exceed_action = RateExceedAction::CANCEL;


    function getValue(mixed $property) : mixed {
        if (property_exists($this, $property)) {
            return $this->{$property};
        }
        throw new DataProcessingException(DataExceptionEnum::PROPERTY_MISSING);
    }

}