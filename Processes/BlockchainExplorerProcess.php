<?php



namespace MyApp\Processes;



class BlockchainExplorerProcess extends \GolosPhpEventListener\app\process\BlockchainExplorerProcess
{
    /**
     * MainProcess constructor.
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->dbManagerClassName = 'MyApp\Db\RedisManager';
        $this->connectorClassName = 'GrapheneNodeClient\Connectors\WebSocket\GolosWSConnector';
    }

    /**
     *
     */
    public function initConnector()
    {
        $this->connector = new $this->connectorClassName(1);
    }
}