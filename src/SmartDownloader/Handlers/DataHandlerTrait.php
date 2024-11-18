<?php

namespace SmartDownloader\Handlers;


trait DataHandlerTrait
{

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    public function __($name)
    {
        return $this->$name;
    }

    public function equals(self  $other): bool
    {
        $thisProperties  = get_object_vars($this);
        $otherProperties = get_object_vars($other);
        $differencies  = array_diff($thisProperties, $otherProperties);
        return count($differencies) === 0;
    }
}
