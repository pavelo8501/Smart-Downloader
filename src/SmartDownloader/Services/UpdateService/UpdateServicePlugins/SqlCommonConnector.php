<?php

namespace SmartDownloader\Services\UpdateService\UpdateConnectorPlugins;

use SmartDownloader\Services\UpdateService\Interfaces\UpdateConnectorPlugins\UpdateConnectorInterface;

abstract class SqlCommonConnector implements UpdateConnectorInterface {

    public function __construct() {
    }

    public function saveData() {
    }

    public function updateData() {
    }

    public function getData():array|null {
        return [];
    }
}
