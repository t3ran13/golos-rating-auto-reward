<?php


namespace MyApp\Db;


class RedisManager extends \GolosPhpEventListener\app\db\RedisManager
{
    /**
     * @param array $data
     *
     * @return int the length of the list after the push operation.
     */
    public function ratingPostRewardAddToQueue($data)
    {
        $status = $this->connect->rPush("myapp:rewards-post-list", json_encode($data, JSON_UNESCAPED_UNICODE));

        return $status;
    }

    /**
     * @return int
     */
    public function ratingPostRewardGetQueueLength()
    {
        return $this->connect->llen("myapp:rewards-post-list");
    }

    /**
     * @return bool|array as key => json, if nothing return false
     */
    public function ratingPostRewardGetFirstFromQueue()
    {
        $json = $this->connect->lIndex("myapp:rewards-post-list", 0);
        return $json === null ? false : json_decode($json, true);
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function ratingPostRewardRemovePostFromQueue($data)
    {
        return $this->connect->lRem("myapp:rewards-post-list", 0, json_encode($data, JSON_UNESCAPED_UNICODE)) === 0 ? false : true;
    }

    /**
     * @param array $data
     *
     * @return int the length of the list after the push operation.
     */
    public function ratingUsersRewardAddToQueue($data)
    {
        $status = $this->connect->rPush("myapp:rewards-users-list", json_encode($data, JSON_UNESCAPED_UNICODE));

        return $status;
    }

    /**
     * @return int
     */
    public function ratingUsersRewardGetQueueLength()
    {
        return $this->connect->llen("myapp:rewards-users-list");
    }

    /**
     * @param int $n
     *
     * @return array list of json
     */
    public function ratingUsersRewardGetFirstNFromQueue($n)
    {
        $listJson = $this->connect->lRange("myapp:rewards-users-list", 0, $n - 1);
        $list = [];

        foreach ($listJson as $json) {
            $list[] = json_decode($json, true);
        }

        return $list;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function ratingUsersRewardRemoveFromQueue($data)
    {
        return $this->connect->lRem("myapp:rewards-users-list", 0, json_encode($data, JSON_UNESCAPED_UNICODE)) === 0 ? false : true;
    }

    /**
     * @param string $userName
     *
     * @return int the length of the list after the push operation.
     */
    public function ratingUsersRewardsStopListAddUser($userName)
    {
        $status = $this->connect->rPush("myapp:rewards-users-stop-list", $userName);

        return $status;
    }

    /**
     * @param string $userName
     *
     * @return bool
     */
    public function ratingUsersRewardsStopListRemoveUser($userName)
    {
        return $this->connect->lRem("myapp:rewards-users-stop-list", 0, $userName) === 0 ? false : true;
    }

    /**
     *
     * @return bool
     */
    public function ratingUsersRewardsStopListGet()
    {
        return $this->connect->lRange("myapp:rewards-users-stop-list", 0, -1);
    }
}