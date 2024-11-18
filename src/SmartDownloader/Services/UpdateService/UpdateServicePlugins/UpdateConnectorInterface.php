<?php

namespace SmartDownloader\Services\UpdateService\UpdateConnectorPlugins;

interface UpdateConnectorInterface {

    public function saveData();

    public function updateData();

    public function requestData();
}
