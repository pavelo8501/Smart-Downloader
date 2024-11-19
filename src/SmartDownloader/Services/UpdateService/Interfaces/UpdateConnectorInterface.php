<?php

namespace SmartDownloader\Services\UpdateService\Interfaces\UpdateConnectorPlugins;

interface UpdateConnectorInterface {

    public function saveData();

    public function updateData();

    public function getData(): array|null;
}
