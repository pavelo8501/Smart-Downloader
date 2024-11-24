<?php

namespace SmartDownloader\Services\UpdateService\Interfaces;

use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;

interface UpdateConnectorInterface {

    public function getTransaction(int $transaction_id): TransactionDataClass | null;

    function saveTransaction(TransactionDataClass $transaction):int;

    function getTransactions(array $transactions): array | null;

    function deleteTransactions(array $transactions): bool;

}
