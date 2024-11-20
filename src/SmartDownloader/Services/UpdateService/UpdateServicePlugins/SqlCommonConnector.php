<?php

namespace SmartDownloader\Services\UpdateService\UpdateServicePlugins;

use SmartDownloader\Services\UpdateService\Interfaces\UpdateConnectorInterface;

abstract class SqlCommonConnector implements UpdateConnectorInterface {

    public function __construct() {
    }

    public function saveData() {
    }

    public function updateData() {
    }

    public function selectData():array|null {
        return [];
    }

    public function pickData():mixed{
        return null;
    }
}
