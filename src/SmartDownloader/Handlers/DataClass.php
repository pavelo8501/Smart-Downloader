<?php

namespace SmartDownloader\Handlers;

use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\DataExceptionEnum;
use SmartDownloader\Handlers\DataHandlerTrait;



/**
 * This class is the base class for all the data classes. Hollo from Kotlin :)
 */
class DataClass
{
    use DataHandlerTrait;

    public function __construct(mixed ...$values)
    {
        if (count($values) === 0) {
            throw new DataProcessingException(DataExceptionEnum::NO_PARAMS);
        }
    }

    public function copy(DataClass $original): self
    {
        $copy = new  DataClass(get_object_vars($original));
        return $copy;
    }
}
