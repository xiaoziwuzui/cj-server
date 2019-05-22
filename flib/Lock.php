<?php

/**
 * Created by PhpStorm.
 * User: JohnsonLi
 * Date: 16/4/20
 * Time: 下午5:50
 * QQ: 505214658
 */
abstract class Lock
{
    abstract public function lock($name, $timeout = 0, $expire = 15, $waitIntervalUs = 100000);

    abstract public function unlock($name);

    abstract public function unlockAll();

    abstract public function isLocking($name);

    abstract public function expire($name, $expire);

    static function newInstance($type = 'redis')
    {
        return new RedisLock();
    }
}


/**
 * Class RedisLock
 * 通过Redis实现php的锁功能
 */
class RedisLock extends Lock
{
    private $prefix = 'Lock_';
    private $redis;
    private $lockedNames = array();

    public function __construct()
    {
        $this->redis = FRedis::getInstance(10)->getRedis();
        $this->redis->select(10);
    }


    /**
     * @param $name 锁的标识名
     * @param int $timeout 循环获取锁的等待超时时间，在此时间内会一直尝试获取锁直到超时，为0表示失败后直接返回不等待
     * @param int $expire 当前锁的最大生存时间(秒)，必须大于0，如果超过生存时间锁仍未被释放，则系统会自动强制释放
     * @param int $waitIntervalUs 获取锁失败后挂起再试的时间间隔(微秒)
     * @return bool
     */
    public function lock($name, $timeout = 5, $expire = 30, $waitIntervalUs = 100000)
    {
        if ($name == null) {
            return false;
        }

        $now = time();
        $timeoutAt = $now + $timeout;
        $expireAt = $now + $expire;

        $redisKey = $this->prefix . $name;

        while (true) {
            // 将rediskey的最大生存时刻存到redis里，过了这个时刻该锁会被自动释放
            $result = $this->redis->setnx($redisKey, $expireAt);

            if ($result) {
                // 防止不同服务期间时间戳不一致
                $this->redis->expire($redisKey, $expire);
                // 将锁标志放到lockedNames数组里
                $this->lockedNames[$name] = $expireAt;
                return true;
            }

            // 不够严谨
            $expireAtTemp = $this->redis->get($redisKey);

            // 防止expire没被执行到，造成死锁。
            if ($expireAtTemp != false && $expireAtTemp < time()) {
                usleep(mt_rand(0, 100000));

                $expireAtTemp = $this->redis->get($redisKey);

                if ($expireAtTemp != false && $expireAtTemp < time()) {
                    $this->redis->expire($redisKey, $expire);
                }
            }

            // 如果没设置锁失败的等待时间 或者 已超过最大等待时间了，那就退出
            if ($timeout <= 0 || $timeoutAt < microtime(true)) {
                break;
            }

            // 隔 $waitIntervalUs 后继续 请求
            usleep(mt_rand($waitIntervalUs / 2, $waitIntervalUs));
        }

        return false;
    }

    /**
     * 解锁
     * @param $name
     * @return bool
     */
    public function unlock($name)
    {
        // 先判断是否存在此锁
        if ($this->isLocking($name)) {
            if ($this->redis->del($this->prefix . $name)) {
                // 清掉lockedNames里的锁标志
                unset($this->lockedNames[$name]);
                return true;
            }
        }

        return false;
    }

    /**
     * 释放当前所有获得的锁
     * @return bool
     */
    public function unlockAll()
    {
        // 此标志是用来标志是否释放所有锁成功
        $allSuccess = true;

        foreach ($this->lockedNames as $name => $expireAt) {
            if (false === $this->unlock($name)) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }


    /**
     * 判断当前是否拥有指定名字的锁
     * @param $name
     * @return bool
     */
    public function isLocking($name)
    {
        // 先看lonkedName[$name]是否存在该锁标志名
        if (isset($this->lockedNames[$name])) {
            // 从redis返回该锁的生存时间
            return (string)$this->lockedNames[$name] == (string)$this->redis->get($this->prefix . $name);
        }

        return false;
    }


    /**
     * 给当前所增加指定生存时间，必须大于0
     * @param $name
     * @param $expire
     * @return bool
     */
    public function expire($name, $expire)
    {
        // 先判断是否存在该锁
        if ($this->isLocking($name)) {
            $expire = max($expire, 1);

            if ($this->redis->expire($this->prefix . $name, $expire)) {
                return true;
            }
        }

        return false;
    }
}
