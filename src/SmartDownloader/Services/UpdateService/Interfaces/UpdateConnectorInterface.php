<?php

namespace SmartDownloader\Services\UpdateService\Interfaces;

interface UpdateConnectorInterface {

    public function saveData();

    public function updateData();

    public function selectData(): array|null;

    public function pickData(): mixed;
}
