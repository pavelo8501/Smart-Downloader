<?php

namespace SmartDownloader\Models;

use SmartDownloader\Enumerators\RateExceedAction;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\DataExceptionEnum;
use SmartDownloader\Exceptions\DataProcessingExceptionCode;

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
    public static string $download_dir = 'downloads';

    /**
     * @var string The directory used for temporary files during downloads.
     */
    public static string $temp_dir = 'temp';

    /**
     * @var int The maximum number of concurrent downloads allowed.
     */
    public static int $max_downloads = 5;

    /**
     * @var int The number of retry attempts if a download fails or process interupted
     */
    public static int $retry_attempts = 5;

    /**
     * @var int The size of each chunk to download in bytes.
     */
    public static int $chunk_size = 1024;

    /**
     * @var RateExceedAction The action to take when the download rate limit is exceeded.
     */
    public static RateExceedAction $rate_exceed_action = RateExceedAction::CANCEL;


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

}