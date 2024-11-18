<?php
namespace SmartDownloader\Services\UpdateService;

use SmartDownloader\Services\UpdateService\UpdateConnectorPlugins\PostgresConnector;
use SmartDownloader\Services\UpdateService\UpdateConnectorPlugins\UpdateConnectorInterface;

class UpdateService{

public PostgresConnector $updatorPlugin;

    public function __construct(UpdateConnectorInterface $plugin){
        if (!is_null(new $plugin)){
            $this->updatorPlugin = $plugin;
        }
    }
}