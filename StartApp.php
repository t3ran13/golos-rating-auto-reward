<?php



namespace MyApp;


use GolosPhpEventListener\app\AppConfig;
use GolosPhpEventListener\app\process\EventsHandlersProcess;
use GolosPhpEventListener\app\process\MainProcess;
use GolosPhpEventListener\app\process\ProcessInterface;
use MyApp\Db\RedisManager;
use MyApp\Handlers\GotTransferHandler;
use MyApp\Handlers\RatingGotRewardHandler;
use MyApp\Processes\BlockchainExplorerProcess;
use MyApp\Processes\RatingRewardUsersQueueMakerProcess;
use MyApp\Processes\RatingRewardUsersSenderProcess;


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('PATH', __DIR__);
require __DIR__ . "/Autoloader.php"; // only in GrapheneNodeClient project
require __DIR__ . '/vendor/autoload.php';

echo PHP_EOL . '------ StartApp.php ------';

$appConfig = new AppConfig();
$appConfig->addListener(['op:1:author' => 't3ran13', 'op:0' => 'author_reward'], new RatingGotRewardHandler());
$appConfig->addListener(['op:1:to' => getenv('REWARD_POOL_NAME'), 'op:0' => 'transfer'], new GotTransferHandler());


$dbRedis = New RedisManager();
$mainProcess = new MainProcess(
    $appConfig,
    $dbRedis
);
//$mainProcess->ClearAllData();

$currentDatetime = (new \DateTime())->sub(new \DateInterval('PT0H30M'))->format('Y-m-d H:i:s');
if (
    $mainProcess->getStatus() === ProcessInterface::STATUS_STOPPED
    || $mainProcess->getStatus() === null
    || (
        $mainProcess->getStatus() === ProcessInterface::STATUS_RUNNING
        && $currentDatetime > $mainProcess->getLastUpdateDatetime()
    )
) {
    echo PHP_EOL . '------ new MainProcess is started ------';

    $className = get_class($dbRedis);
    $blockchainExplorerProcess = new BlockchainExplorerProcess($className);
    $blockchainExplorerProcess->setLastBlock(getenv('LAST_BLOCK'));

    $mainProcess->processesList = [
        $blockchainExplorerProcess,
        new EventsHandlersProcess($className),
        new RatingRewardUsersQueueMakerProcess(),
        new RatingRewardUsersSenderProcess()
    ];


    try {
        $mainProcess->start();

    } catch (\Exception $e) {

        $msg = '"' . $e->getMessage() . '" ' . $e->getTraceAsString();
        echo PHP_EOL . ' --- mainProcess got exception ' . $msg . PHP_EOL;
        $mainProcess->errorInsertToLog(date('Y-m-d H:i:s') . '   ' . $msg);

    } finally {

        $mainProcess->setStatus(ProcessInterface::STATUS_STOPPED);
        exit(1);
    }
} else {
    echo PHP_EOL . '------ other StartApp.php is working ------';
}