<?php

namespace SmartDownloader\Models;

use SmartDownloader\Enumerators\RateExceedAction;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\DataProcessingExceptionCode;

enum ChinkSize: int{
    case MB_1 =  1048576;
    case MB_2  = 2097152;
    case MB_5 =  5242880;
    case MB_10 = 10485760;
    case MB_15 = 15728640;
}


/**
 * Class SDConfiguration
 * 
 * This class holds configuration settings for the Smart Downloader.
 * It provides static properties for default values and a method to retrieve property values dynamically.
 */
class SDConfiguration{
    /**
     * @var string The directory where downloaded files will be saved.
     */
    public string $download_dir = 'downloads';

    /**
     * @var string The directory used for temporary files during downloads.
     */
    public string $temp_dir = 'temp';

    /**
     * @var int The maximum number of concurrent downloads allowed.
     */
    public int $max_downloads = 5;

    /**
     * @var int The number of retry attempts if a download fails or process interupted
     */
    public int $retry_attempts = 5;

    /**
     * @var int The size of each chunk to download in bytes.
     */
    public ChinkSize $chunk_size = ChinkSize::MB_2;

    /**
     * @var RateExceedAction The action to take when the download rate limit is exceeded.
     */
    public RateExceedAction $rate_exceed_action = RateExceedAction::CANCEL;
    /**
     * Retrieves the value of a specified property.
     * 
     * @param mixed $property The property name to retrieve.
     * 
     * @return mixed The value of the property.
     * 
     * @throws DataProcessingException If the property does not exist.
     */
    function getValue(mixed $property) : mixed {
        if (property_exists($this, $property)) {
            return $this->{$property};
        }
        throw new DataProcessingException("No such key in the configuration", DataProcessingExceptionCode::PROPERTY_MISSING);
    }
    function getConfigurationArray():array{
         return  [
            "download_dir"=> $this->download_dir,
            "temp_dir" => $this->temp_dir,
            "max_downloads" => $this->max_downloads
         ];
    }
}