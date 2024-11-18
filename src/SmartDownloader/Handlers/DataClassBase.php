<?php

namespace SmartDownloader\Handlers;

use SmartDownloader\Handlers\DataHandlerTrait;
use SmartDownloader\Handlers\DataClass;

abstract class DataClassBase
{

    use DataHandlerTrait;

    public $properties = [];

    public function __construct(mixed ...$values)
    {
        $this->properties = $values;
    }

    public function copy(DataClass $original): DataClass
    {
        $copy = new  DataClass(get_object_vars($original));
        return $copy;
    }
}
