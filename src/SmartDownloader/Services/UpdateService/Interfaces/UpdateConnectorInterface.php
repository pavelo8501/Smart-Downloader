<?php

namespace SmartDownloader\Services\UpdateService\Interfaces;

use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;

interface UpdateConnectorInterface {

    public function saveData();

    public function updateData();

    public function selectData(): array|null;

    public function pickData(): mixed;

    function saveTransaction(TransactionDataClass $transaction):int;

    function getTransactions(): array | null;


}
