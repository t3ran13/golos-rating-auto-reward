<?php


namespace MyApp\Handlers;


use GolosPhpEventListener\app\handlers\HandlerAbstract;
use GolosPhpEventListener\app\process\ProcessInterface;
use MyApp\Db\RedisManager;

/**
 *
 * @method RedisManager getDBManager()
 */
class RatingGotRewardHandler extends HandlerAbstract
{
    protected $priority = 15;

    /**
     * run before process start
     *
     * @return void
     */
    public function init()
    {
        $this->setDBManager(new RedisManager());
    }


    public function initSignalsHandlers()
    {
        pcntl_signal(SIGTERM, [$this, 'signalsHandlers']);
        pcntl_signal(SIGINT, [$this, 'signalsHandlers']); //ctrl+c
        pcntl_signal(SIGHUP, [$this, 'signalsHandlers']); //restart process
    }

    public function signalsHandlers($signo, $signinfo)
    {
        echo PHP_EOL . ' --- process with pid=' . $this->getPid() . ' got signal=' . $signo . ' and signinfo='
            . print_r($signinfo, true);

        switch ($signo) {
            case SIGTERM:
                $this->isRunning = false;
                break;
            default:
        }
    }

    public function start()
    {
        pcntl_setpriority($this->priority, getmypid());

        $listenerId = $this->getId();
//        echo PHP_EOL . ' --- listener with id/pid=' . $listenerId . '/' . $this->getPid() . ' is running';
        $events = $this->getDBManager()->eventsListByListenerId($listenerId);
        foreach ($events as $key => $event) {
            $event = json_decode($event, true);
            $blockN = $event['block'];
            $trxInBlock = $event['trx_in_block'];
            $this->getDBManager()->eventDelete($listenerId, $blockN, $trxInBlock);
//            echo PHP_EOL . ' --- listener with id=' . $listenerId . ' handle and deleted event with key=' . $key;

            if (
                isset($event['op'][1]['permlink'])
                && strpos($event['op'][1]['permlink'], 'alternativnyi-top-golosa') === 0
            ) {
                $this->getDBManager()->ratingPostRewardAddToQueue(
                    [
                        'author'       => $event['op'][1]['author'],
                        'permlink'     => $event['op'][1]['permlink'],
                        'sbd_payout'   => $event['op'][1]['sbd_payout'],
                        'steem_payout' => $event['op'][1]['steem_payout']
                    ]
                );
            }
        }

        $eventsTotal = count($events);
        echo PHP_EOL . date('Y.m.d H:i:s') .  " RatingGotRewardHandler handled {$eventsTotal} events";
    }

    /**
     * ask process to start
     *
     * @return bool
     */
    public function isStartNeeded()
    {
        $status = $this->getStatus();
        return $status === ProcessInterface::STATUS_RUN
            || (
                $status === ProcessInterface::STATUS_STOPPED
                && $this->getMode() === ProcessInterface::MODE_REPEAT
                && $this->getDBManager()->eventsCountByListenerId($this->getId()) > 0
            );
    }
}